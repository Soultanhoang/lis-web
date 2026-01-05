<?php

namespace App\Providers;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
       $this->registerPolicies();

    // --- BẮT ĐẦU PHÂN QUYỀN ---

    // Gate cho Admin: Trả về true NẾU user->role là 'admin'
    Gate::define('is-admin', function(User $user) {
        return $user->role === 'admin';
    });

    // Gate cho Bác sĩ:
    Gate::define('is-doctor', function(User $user) {
        return $user->role === 'doctor';
    });

    // Gate cho Kỹ thuật viên (KTV):
    Gate::define('is-technician', function(User $user) {
        return $user->role === 'technician';
    });

    // Gate cho Bác sĩ HOẶC Admin (ví dụ: dùng để xem danh sách bệnh nhân)
    Gate::define('is-doctor-or-admin', function(User $user) {
        return in_array($user->role, ['doctor', 'admin']);
    });

    // --- KẾT THÚC PHÂN QUYỀN ---
    }
}
