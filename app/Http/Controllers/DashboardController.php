<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $base = Ticket::visibleTo($request->user());
        $counts = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $recent = (clone $base)->with(['creator', 'category', 'assignee'])->latest('updated_at')->limit(6)->get();
        $urgent = (clone $base)->whereIn('status', ['aberto', 'em_atendimento'])->whereIn('priority', ['alta', 'urgente'])->count();

        return view('dashboard', compact('counts', 'recent', 'urgent'));
    }
}
