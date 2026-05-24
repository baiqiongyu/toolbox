<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'user:create {name} {email} {password?}';
    protected $description = '创建后台用户账号';

    public function handle(): int
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password') ?? 'password123';

        if (User::where('email', $email)->exists()) {
            $this->error("邮箱 {$email} 已存在！");
            return Command::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("✅ 用户创建成功！");
        $this->table(['ID', '姓名', '邮箱', '密码'], [
            [$user->id, $name, $email, $this->argument('password') ? '已设置' : 'password123'],
        ]);

        return Command::SUCCESS;
    }
}
