<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class GenApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-token:generate {user : 회원코드}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $id_alias = $this->argument('user');
        $user = User::findByIdAlias($id_alias);
        if(!$user) {
            $this->warn('User not found.');
            return command::FAILURE;
        }

        $user->api_token = User::genApiToken($user);
        $user->save();
        $this->info("Generated Api token : {$user->api_token}\nfor {$user->id_alias}");
        return Command::SUCCESS;
    }
}
