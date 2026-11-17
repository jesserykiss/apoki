<?php

declare(strict_types=1);

namespace Apoki\Services;

use Apoki\Core\ValidationException;
use Apoki\DAO\UserDao;
use DomainException;

final class AuthService
{
    public function __construct(private readonly UserDao $userDao)
    {
    }

    public function register(array $data): array
    {
        $errors = \Apoki\Core\Validator::validate($data, [
            'username' => ['required', 'regex:/^[A-Za-z0-9_]{3,30}$/'],
            'password' => ['required', 'min:8'],
            'nickname' => ['required', 'max:50'],
            'phone' => ['max:30'],
            'email' => ['email', 'max:190'],
        ]);

        $password = (string) ($data['password'] ?? '');
        if (strlen($password) > 72) {
            $errors['password'] = '密码不能超过 72 个字节。';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $username = (string) $data['username'];
        if ($this->userDao->findByUsername($username) !== null) {
            throw new DomainException('该用户名已被使用。');
        }

        $id = $this->userDao->create([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'nickname' => trim((string) $data['nickname']),
            'phone' => trim((string) ($data['phone'] ?? '')),
            'email' => trim((string) ($data['email'] ?? '')),
        ]);

        return $this->publicUser((array) $this->userDao->findById($id));
    }

    public function authenticate(string $username, string $password): array
    {
        $user = $this->userDao->findByUsername($username);
        if ($user === null || (int) $user['status'] !== 1 || !password_verify($password, (string) $user['password_hash'])) {
            throw new DomainException('用户名或密码不正确。');
        }

        if (password_needs_rehash((string) $user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $this->userDao->updatePassword((int) $user['id'], password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
            $user['updated_at'] = $this->userDao->findUpdatedAt((int) $user['id']);
        }

        return $this->publicUser($user);
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword, string $confirmation): string
    {
        if (strlen($newPassword) < 8 || strlen($newPassword) > 72) {
            throw new ValidationException(['new_password' => '新密码长度应为 8 至 72 个字节。']);
        }

        if ($newPassword !== $confirmation) {
            throw new ValidationException(['confirm_password' => '两次输入的新密码不一致。']);
        }

        $user = $this->userDao->findById($userId);
        if ($user === null || !password_verify($currentPassword, (string) $user['password_hash'])) {
            throw new DomainException('原密码不正确。');
        }

        $this->userDao->updatePassword($userId, password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]));
        return (string) $this->userDao->findUpdatedAt($userId);
    }

    public function sessionIsValid(int $userId, string $sessionUpdatedAt): bool
    {
        $user = $this->userDao->findById($userId);
        return $user !== null
            && (int) $user['status'] === 1
            && (string) ($user['updated_at'] ?? '') === $sessionUpdatedAt;
    }

    private function publicUser(array $user): array
    {
        unset($user['password_hash']);
        return $user;
    }
}
