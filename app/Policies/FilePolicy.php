<?php

namespace App\Policies;

use App\Models\UploadedFile;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FilePolicy
{
    public function download(User $user, UploadedFile $file)
    {
        return $user->id === $file->user_id;
    }
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, UploadedFile $uploadedFile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UploadedFile $uploadedFile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UploadedFile $uploadedFile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UploadedFile $uploadedFile): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UploadedFile $uploadedFile): bool
    {
        return false;
    }
}
