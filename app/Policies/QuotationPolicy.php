<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuotationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Quotation $quotation): bool
    {
        if ($user->isSuperAdmin() || $user->role === User::ROLE_ADMIN || $user->role === User::ROLE_MANAGER) {
            return true;
        }

        return $quotation->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Quotation $quotation): bool
    {
        if ($user->isSuperAdmin() || $user->role === User::ROLE_ADMIN || $user->role === User::ROLE_MANAGER) {
            return true;
        }

        return $quotation->user_id === $user->id
            && in_array($quotation->status, ['draft', 'sent'], true);
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        if ($user->isSuperAdmin() || $user->role === User::ROLE_ADMIN) {
            return true;
        }

        if ($user->role === User::ROLE_MANAGER) {
            return in_array($quotation->status, ['draft'], true);
        }

        return false;
    }

    public function pdf(User $user, Quotation $quotation): bool
    {
        return $this->view($user, $quotation);
    }

    public function send(User $user, Quotation $quotation): bool
    {
        if ($user->isSuperAdmin() || $user->role === User::ROLE_ADMIN || $user->role === User::ROLE_MANAGER) {
            return true;
        }

        if ($user->role === 'sales_rep') {
            return $quotation->user_id === $user->id
                && $quotation->status === 'draft';
        }

        return false;
    }
}