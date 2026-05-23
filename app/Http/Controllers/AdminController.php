<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::all();

        $projects = Project::active()->get();
        $archivedProjects = Project::archived()->get();

        return view('admin.users', [
            'users' => $users,
            'projects'  => $projects,
            'archivedProjects' => $archivedProjects,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();
        $pwd = $this->generatePwd();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'sys_role' => $validated['sys_role'] ?? 'user',
            'password' => Hash::make($pwd),
        ]);

        /*
         *  Controllo sulla mail try/catch se la mail non va a buon fine.
         * Il controllo è necessario perchè le mail vengono mandate in modo sincrono per far funzionare lo scheduler,
         * con un approccio asincrono questo controllo non è necessario
         * */
        $emailSent = true;
        try {
            $user->sendUserRegistrationNotification(auth()->user(), $pwd);
        } catch (\Throwable $th) {
            $emailSent = false;
            report($th); // logga l'errore
        }

        return response()->json([
                'user' => $user,
                'email_sent' => $emailSent,
            ], 201);
    }

    public function update(UpdateUserRequest $request, User $user){
        $validated = $request->validated();

        $adminsCount = User::where('sys_role', 'admin')->count();
        if ($user->sys_role === 'admin'
            && $adminsCount === 1
            // ?? $user->sys_role serve per avere un valore valido se l'utente non inserisce un ruolo (poco probabile ma megio controllare)
            && ($validated['sys_role'] ?? $user->sys_role) === 'user') {
                return response()->json(
                    ['errors' =>
                        ['sys_role' => ['Non puoi modificare il ruolo all\'ultimo admin']]
                    ], 422
                );
        }

        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
            'sys_role' => $validated['sys_role'] ?? $user->sys_role,
        ]);

        return response()->json(['user' => $user], 200);
    }

    public function destroy(User $user){
        $adminsCount = User::where('sys_role', 'admin')->count();
        if ($user->sys_role === 'admin' && $adminsCount === 1) {
            return response()->json(
                ['message' => 'Non puoi eliminare l\'ultimo admin' ],
                422
            );
        }
        $user->delete();
        return response()->json(['ok' => true], 200);
    }

    // helper per la generazione di password
    private function generatePwd(): string
    {
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers    = '0123456789';
        $all     = $lower . $upper . $numbers;

        $pass[] = $lower[random_int(0, 25)];
        $pass[] = $upper[random_int(0, 25)];
        $pass[] = $numbers[random_int(0, 9)];

        for ($i = 0; $i < 5; $i++) {
            $pass[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($pass);
        return implode('', $pass);
    }
}
