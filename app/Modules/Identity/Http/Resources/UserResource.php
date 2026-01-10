<?php

namespace App\Modules\Identity\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(function ($role) {
                    $roleData = [
                        'id' => $role->id,
                        'name' => $role->name,
                    ];

                    if ($role->relationLoaded('permissions')) {
                        $roleData['permissions'] = $role->permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                            ];
                        })->values();
                    }

                    return $roleData;
                })->values();
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
