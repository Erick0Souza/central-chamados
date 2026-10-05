<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => 'nullable|string|max:150', 'status' => ['nullable', Rule::in(array_keys(Ticket::STATUSES))], 'priority' => ['nullable', Rule::in(array_keys(Ticket::PRIORITIES))], 'category_id' => 'nullable|integer|exists:categories,id']);
        $query = Ticket::visibleTo($request->user())->with(['creator', 'category', 'assignee']);
        if ($term = $filters['q'] ?? null) {
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', '%'.$term.'%');
                if (preg_match('/^(?:CH-)?0*(\d+)$/i', $term, $m)) {
                    $q->orWhere('id', (int) $m[1]);
                }
            });
        }
        foreach (['status', 'priority', 'category_id'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }
        $tickets = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('tickets.index', ['tickets' => $tickets, 'categories' => Category::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('tickets.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['title' => 'required|string|min:5|max:150', 'description' => 'required|string|min:15|max:10000', 'category_id' => 'required|integer|exists:categories,id', 'priority' => ['required', Rule::in(array_keys(Ticket::PRIORITIES))], 'attachment' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf,txt|max:5120']);
        $path = null;
        try {
            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('attachments', 'local');
                abort_if(! $path, 500, 'Não foi possível salvar o anexo.');
            }
            $ticket = DB::transaction(function () use ($request, $data, $path) {
                $ticket = new Ticket($data);
                $ticket->user_id = $request->user()->id;
                $ticket->status = 'aberto';
                $ticket->save();
                $ticket->activities()->create(['user_id' => $request->user()->id, 'description' => 'Chamado aberto.']);
                if ($path) {
                    $file = $request->file('attachment');
                    $ticket->attachments()->create(['user_id' => $request->user()->id, 'path' => $path, 'original_name' => mb_substr(basename($file->getClientOriginalName()), 0, 240), 'size' => $file->getSize()]);
                }

                return $ticket;
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            } throw $e;
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Chamado aberto com sucesso.');
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        $ticket->load(['creator', 'assignee', 'category', 'attachments']);

        return view('tickets.show', ['ticket' => $ticket, 'comments' => $ticket->comments()->with('user')->oldest()->paginate(15, ['*'], 'conversa'), 'activities' => $ticket->activities()->with('user')->latest()->paginate(10, ['*'], 'historico'), 'technicians' => User::where('role', 'tecnico')->orderBy('name')->get()]);
    }

    public function comment(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        $data = $request->validate(['body' => 'required|string|min:2|max:5000']);
        DB::transaction(function () use ($request, $ticket, $data) {
            $locked = Ticket::lockForUpdate()->findOrFail($ticket->id);
            Gate::authorize('comment', $locked);
            $locked->comments()->create(['body' => $data['body'], 'user_id' => $request->user()->id]);
            $locked->touch();
        });

        return back()->with('success', 'Comentário enviado.');
    }

    public function status(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Ticket::STATUSES))]]);
        DB::transaction(function () use ($request, $ticket, $data) {
            $locked = Ticket::lockForUpdate()->findOrFail($ticket->id);
            Gate::authorize('view', $locked);
            if (! in_array($data['status'], $locked->transitionsFor($request->user()), true)) {
                throw ValidationException::withMessages(['status' => 'Esta mudança de status não é permitida. Atualize a página.']);
            }
            if ($data['status'] === 'em_atendimento' && ! $locked->assigned_to) {
                throw ValidationException::withMessages(['status' => 'Atribua um técnico antes de iniciar o atendimento.']);
            }
            $old = $locked->statusLabel();
            $locked->status = $data['status'];
            $locked->save();
            $locked->activities()->create(['user_id' => $request->user()->id, 'description' => "Status alterado de {$old} para {$locked->statusLabel()}."]);
        });

        return back()->with('success', 'Status atualizado.');
    }

    public function close(Request $request, Ticket $ticket)
    {
        Gate::authorize('close', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $locked = Ticket::lockForUpdate()->findOrFail($ticket->id);
            Gate::authorize('close', $locked);

            $locked->status = 'encerrado';
            $locked->save();
            $locked->activities()->create([
                'user_id' => $request->user()->id,
                'description' => 'Chamado encerrado.',
            ]);
        });

        return back()->with('success', 'Chamado encerrado.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        Gate::authorize('assign', $ticket);
        $data = $request->validate(['assigned_to' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'tecnico')]]);
        DB::transaction(function () use ($request, $ticket, $data) {
            $locked = Ticket::lockForUpdate()->findOrFail($ticket->id);
            Gate::authorize('assign', $locked);
            $technician = User::where('role', 'tecnico')->findOrFail($data['assigned_to']);
            if ($locked->assigned_to === $technician->id) {
                return;
            }
            $locked->assigned_to = $technician->id;
            $locked->save();
            $locked->activities()->create(['user_id' => $request->user()->id, 'description' => 'Atendimento atribuído a '.$technician->name.'.']);
        });

        return back()->with('success', 'Responsável atualizado.');
    }

    public function download(Attachment $attachment)
    {
        Gate::authorize('view', $attachment->ticket);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->original_name, ['X-Content-Type-Options' => 'nosniff']);
    }
}
