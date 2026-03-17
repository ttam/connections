<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        if (\auth()->user()->email === \config('admin.email')) {
            return \redirect()->intended(\route('filament.admin.pages.dashboard'));
        }

        return \redirect()->route('archive');
    }
}
