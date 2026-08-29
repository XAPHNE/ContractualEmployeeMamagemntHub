<?php

namespace App\Rules;

use App\Models\PasswordHistory;
use App\Models\Setting;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;

class PasswordHistoryRule implements ValidationRule
{
    public function __construct(protected ?int $userId = null)
    {
        $this->userId ??= auth()->id();
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $historyLimit = (int) Setting::get('password_history_limit', 0);

        if ($historyLimit <= 0 || ! $this->userId) {
            return;
        }

        $histories = PasswordHistory::where('user_id', $this->userId)
            ->latest()
            ->take($historyLimit)
            ->get();

        foreach ($histories as $history) {
            if (Hash::check($value, $history->password)) {
                $fail("You cannot reuse any of your last {$historyLimit} passwords.");
                return;
            }
        }
    }
}
