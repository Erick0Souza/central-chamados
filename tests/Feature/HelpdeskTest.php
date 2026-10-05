<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HelpdeskTest extends TestCase
{
    use RefreshDatabase;

    private function person(string $role = 'cliente'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function ticket(User $user, ?User $tech = null, string $status = 'aberto'): Ticket
    {
        $category = Category::firstOrCreate(['name' => 'Sistemas']);
        $ticket = new Ticket(['title' => 'Problema no portal interno', 'description' => 'Não consigo acessar o portal de vendas da empresa.', 'category_id' => $category->id, 'priority' => 'media']);
        $ticket->user_id = $user->id;
        $ticket->assigned_to = $tech?->id;
        $ticket->status = $status;
        $ticket->save();

        return $ticket;
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/painel')->assertRedirect('/entrar');
        $this->get('/chamados')->assertRedirect('/entrar');
        $this->get('/entrar')->assertOk()->assertSee('Entrar');
    }

    public function test_public_registration_cannot_assign_admin_role(): void
    {
        $this->post('/cadastro', ['name' => 'Erick', 'email' => 'erick@example.com', 'password' => 'Senha12345', 'password_confirmation' => 'Senha12345', 'role' => 'admin'])->assertRedirect('/painel');
        $this->assertDatabaseHas('users', ['email' => 'erick@example.com', 'role' => 'cliente']);
        $this->assertAuthenticated();
    }

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create(['password' => 'Senha12345']);
        $this->post('/entrar', ['email' => $user->email, 'password' => 'errada'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/entrar', ['email' => $user->email, 'password' => 'Senha12345'])->assertRedirect('/painel');
        $this->assertAuthenticatedAs($user);
        $this->post('/sair')->assertRedirect('/entrar');
        $this->assertGuest();
    }

    public function test_login_rate_limit_blocks_even_a_correct_password_after_five_failures(): void
    {
        $user = User::factory()->create(['password' => 'Senha12345']);
        for ($i = 0; $i < 5; $i++) {
            $this->post('/entrar', ['email' => $user->email, 'password' => 'incorreta'])->assertSessionHasErrors();
        }
        $this->post('/entrar', ['email' => $user->email, 'password' => 'Senha12345'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_client_cannot_read_or_comment_on_another_clients_ticket(): void
    {
        $ticket = $this->ticket($this->person());
        $client = $this->person();
        $this->actingAs($client)->get('/chamados/'.$ticket->id)->assertForbidden();
        $this->post('/chamados/'.$ticket->id.'/comentarios', ['body' => 'Teste de comentário'])->assertForbidden();
        $this->get('/chamados')->assertOk()->assertDontSee($ticket->title);
        $this->get('/painel')->assertOk()->assertDontSee($ticket->title);
    }

    public function test_technician_only_sees_assigned_or_own_tickets(): void
    {
        $tech = $this->person('tecnico');
        $visible = $this->ticket($this->person(), $tech);
        $hidden = $this->ticket($this->person());
        $this->actingAs($tech)->get('/chamados/'.$visible->id)->assertOk();
        $this->get('/chamados/'.$hidden->id)->assertForbidden();
        $this->get('/usuarios')->assertForbidden();
    }

    public function test_admin_can_render_all_main_pages(): void
    {
        $ticket = $this->ticket($this->person());
        $admin = $this->person('admin');
        foreach (['/painel', '/chamados', '/chamados/novo', '/chamados/'.$ticket->id, '/usuarios'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_ticket_creation_ignores_forged_owner_status_and_assignment(): void
    {
        $user = $this->person();
        $other = $this->person();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs($user)->post('/chamados', ['title' => 'Erro ao acessar meu e-mail', 'description' => 'O sistema está retornando um erro de acesso.', 'priority' => 'alta', 'category_id' => Category::first()->id, 'user_id' => $other->id, 'assigned_to' => $other->id, 'status' => 'encerrado'])->assertRedirect();
        $this->assertDatabaseHas('tickets', ['user_id' => $user->id, 'assigned_to' => null, 'status' => 'aberto', 'priority' => 'alta']);
    }

    public function test_ticket_validation_rejects_invalid_data(): void
    {
        $this->actingAs($this->person())->post('/chamados', ['title' => 'A', 'description' => 'x', 'priority' => 'inexistente', 'category_id' => 999])->assertSessionHasErrors(['title', 'description', 'priority', 'category_id']);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_admin_assigns_technician_and_audits_it(): void
    {
        $ticket = $this->ticket($this->person());
        $tech = $this->person('tecnico');
        $this->actingAs($this->person('admin'))->patch('/chamados/'.$ticket->id.'/responsavel', ['assigned_to' => $tech->id])->assertRedirect();
        $this->assertSame($tech->id, $ticket->fresh()->assigned_to);
        $this->assertDatabaseCount('activities', 1);
    }

    public function test_assignment_requires_admin_and_technician_target(): void
    {
        $client = $this->person();
        $ticket = $this->ticket($client);
        $tech = $this->person('tecnico');
        $this->actingAs($client)->patch('/chamados/'.$ticket->id.'/responsavel', ['assigned_to' => $tech->id])->assertForbidden();
        $this->actingAs($this->person('admin'))->patch('/chamados/'.$ticket->id.'/responsavel', ['assigned_to' => $client->id])->assertSessionHasErrors('assigned_to');
    }

    public function test_full_lifecycle_and_closed_comment_protection(): void
    {
        $client = $this->person();
        $tech = $this->person('tecnico');
        $ticket = $this->ticket($client, $tech);
        $this->actingAs($tech)->patch('/chamados/'.$ticket->id.'/status', ['status' => 'em_atendimento'])->assertRedirect();
        $this->assertSame('em_atendimento', $ticket->fresh()->status);
        $this->post('/chamados/'.$ticket->id.'/comentarios', ['body' => 'Verificamos e ajustamos a configuração.'])->assertRedirect();
        $this->patch('/chamados/'.$ticket->id.'/status', ['status' => 'resolvido'])->assertRedirect();
        $this->actingAs($client)->patch('/chamados/'.$ticket->id.'/status', ['status' => 'encerrado'])->assertRedirect();
        $this->assertSame('encerrado', $ticket->fresh()->status);
        $this->post('/chamados/'.$ticket->id.'/comentarios', ['body' => 'Comentário tardio'])->assertForbidden();
        $this->assertDatabaseCount('comments', 1);
        $this->assertDatabaseCount('activities', 3);
    }

    public function test_invalid_transitions_and_unassigned_work_are_rejected(): void
    {
        $client = $this->person();
        $ticket = $this->ticket($client);
        $this->actingAs($client)->patch('/chamados/'.$ticket->id.'/status', ['status' => 'resolvido'])->assertSessionHasErrors('status');
        $this->actingAs($this->person('admin'))->patch('/chamados/'.$ticket->id.'/status', ['status' => 'em_atendimento'])->assertSessionHasErrors('status');
        $this->assertSame('aberto', $ticket->fresh()->status);
    }

    public function test_private_attachment_download_is_authorized(): void
    {
        Storage::fake('local');
        $client = $this->person();
        $this->seed(DatabaseSeeder::class);
        $this->actingAs($client)->post('/chamados', ['title' => 'Erro com arquivo de texto', 'description' => 'O arquivo de texto não abre corretamente no sistema.', 'priority' => 'media', 'category_id' => Category::first()->id, 'attachment' => UploadedFile::fake()->createWithContent('detalhes.txt', 'Erro de acesso ao portal.')])->assertRedirect();
        $attachment = Attachment::firstOrFail();
        $this->assertTrue(Storage::disk('local')->exists($attachment->path));
        $this->get('/anexos/'.$attachment->id)->assertDownload('detalhes.txt');
        $this->actingAs($this->person())->get('/anexos/'.$attachment->id)->assertForbidden();
    }

    public function test_executable_attachments_and_oversized_files_are_rejected(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);
        $this->actingAs($this->person());
        $data = ['title' => 'Erro ao abrir arquivo', 'description' => 'Descrição do problema para verificar anexos.', 'priority' => 'media', 'category_id' => Category::first()->id];
        $this->post('/chamados', $data + ['attachment' => UploadedFile::fake()->create('executavel.exe', 10, 'application/x-msdownload')])->assertSessionHasErrors('attachment');
        $this->post('/chamados', $data + ['attachment' => UploadedFile::fake()->create('grande.pdf', 6000, 'application/pdf')])->assertSessionHasErrors('attachment');
    }

    public function test_html_in_comments_is_escaped(): void
    {
        $user = $this->person();
        $ticket = $this->ticket($user);
        $this->actingAs($user)->post('/chamados/'.$ticket->id.'/comentarios', ['body' => '<script>alert("xss")</script>'])->assertRedirect();
        $this->get('/chamados/'.$ticket->id)->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_only_admin_can_create_staff(): void
    {
        $data = ['name' => 'Novo técnico', 'email' => 'novo@central.test', 'password' => 'Senha12345', 'password_confirmation' => 'Senha12345', 'role' => 'tecnico'];
        $this->actingAs($this->person())->post('/usuarios', $data)->assertForbidden();
        $this->actingAs($this->person('admin'))->post('/usuarios', $data)->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'novo@central.test', 'role' => 'tecnico']);
    }

    public function test_resolved_ticket_can_be_closed(): void
    {
        $client = $this->person();
        $ticket = $this->ticket($client, null, 'resolvido');

        $this->actingAs($client)
            ->patch('/chamados/'.$ticket->id.'/encerrar')
            ->assertRedirect();

        $this->assertSame('encerrado', $ticket->fresh()->status);
        $this->assertDatabaseHas('activities', [
            'ticket_id' => $ticket->id,
            'user_id' => $client->id,
            'description' => 'Chamado encerrado.',
        ]);
    }

    public function test_admin_can_delete_unused_user(): void
    {
        $admin = $this->person('admin');
        $user = $this->person();

        $this->actingAs($admin)
            ->delete('/usuarios/'.$user->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->person('admin');

        $this->actingAs($admin)
            ->delete('/usuarios/'.$admin->id)
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_user_with_history_cannot_be_deleted(): void
    {
        $admin = $this->person('admin');
        $user = $this->person();
        $this->ticket($user);

        $this->actingAs($admin)
            ->delete('/usuarios/'.$user->id)
            ->assertSessionHasErrors('user');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_search_and_status_filter(): void
    {
        $client = $this->person();
        $ticket = $this->ticket($client);
        $this->actingAs($client)->get('/chamados?q='.$ticket->code())->assertOk()->assertSee($ticket->title);
        $this->get('/chamados?status=resolvido')->assertOk()->assertDontSee($ticket->title);
    }

    public function test_demo_seeding_twice_preserves_data(): void
    {
        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('tickets', 0);
    }

    public function test_email_registration_is_normalized_and_case_insensitive(): void
    {
        User::factory()->create(['email' => 'erick@example.com']);
        $this->post('/cadastro', ['name' => 'Erick', 'email' => 'ERICK@example.com', 'password' => 'Senha12345', 'password_confirmation' => 'Senha12345'])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }
}
