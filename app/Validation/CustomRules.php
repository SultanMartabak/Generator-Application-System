<?php

namespace App\Validation;

use App\Models\UserModel;

class CustomRules
{
    /**
     * Checks if the old password matches the one in the database.
     *
     * @param string $str    The old password from the form.
     * @param string $userId The user's ID.
     *
     * @return bool
     */
    public function old_password_check(string $str, string $userId): bool
    {
        $userModel = new UserModel();
        // The user ID is passed directly as the first parameter to the rule.
        // The signature is changed to reflect this for clarity.
        $user = $userModel->find($userId);

        if (!$user || !password_verify($str, $user['password_hash'])) {
            return false;
        }

        return true;
    }
}