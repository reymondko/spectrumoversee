<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /**Admin Gates */
        Gate::define('admin-only', function ($user) {
            if($user->role == 1)
            {
                return true;
            }
            return false;
        });


        /**Company Gates*/

        Gate::define('company-only', function ($user) {
            if($user->role == 2 && $user->companies->allow_only_tpl == 0)
            {
                return true;
            }
            return false;
        });

        Gate::define('company-with-third-party', function ($user) {
            if(isset($user->companies))
            {
                if($user->permissions){
                    foreach($user->permissions as $p){
                        if($p['permission_name'] == 'can_see_thirdparty_orders' && $p['enabled'] == 1){
                            return true;
                        }
                    }
                }
            }
            return false;
        });

        /**User Gates*/

        Gate::define('user-only', function ($user) {
            if($user->role == 3)
            {
                return true;
            }
            return false;
        });

        Gate::define('can_login_as', function ($user) {
                if($user->permissions){
                    foreach($user->permissions as $p){
                        if($p['permission_name'] == 'can_login_as' && $p['enabled'] == 1){
                            return true;
                        }
                    }
                }

            return false;
        });

        Gate::define('can_see_thirdparty_orders', function ($user) {
                if($user->permissions){
                    foreach($user->permissions as $p){
                        if($p['permission_name'] == 'can_see_thirdparty_orders' && $p['enabled'] == 1){
                            return true;
                        }
                    }
                }
            return false;
        });

        Gate::define('can_see_inventory', function ($user) {
                if($user->permissions){
                    foreach($user->permissions as $p){
                        if($p['permission_name'] == 'can_see_inventory' && $p['enabled'] == 1){
                            return true;
                        }
                    }
                }
            return false;
        });

        Gate::define('can_see_inventory_import', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_inventory_import' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_inventory_scan', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_inventory_scan' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_kit_sync_report', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_kit_sync_report' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_kit_return_sync', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_kit_return_sync' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_kit_boxing', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_kit_boxing' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_delete_inventory', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_delete_inventory' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_orders', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_orders' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_fulfill_orders', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_fulfill_orders' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_add_orders', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_add_orders' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_third_party_orders', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_third_party_orders' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_ship_pack', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_ship_pack' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_quality_inspector', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_quality_inspector' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_reallocate', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_reallocate' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });

        Gate::define('can_see_kpi_report', function ($user) {
            if($user->permissions){
                foreach($user->permissions as $p){
                    if($p['permission_name'] == 'can_see_kpi_report' && $p['enabled'] == 1){
                        return true;
                    }
                }
            }
            return false;
        });



        /**Allow 3PL Only */
        Gate::define('tpl-only',function ($user) {
            if(isset($user->companies->allow_only_tpl))
            if($user->companies->allow_only_tpl == 1){
                {
                    return true;
                }
            }
            return false;
        });

        /**Customer Gates */
        Gate::define('customer-only', function ($user) {
            if($user->role == 4)
            {
                return true;
            }
            return false;
        });
    }
}
