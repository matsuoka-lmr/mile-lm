<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Auth\Authorizable;
use App\Consts\AuthConsts;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'campany_id', 'role', 'name', 'email', 'phone', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password'
    ];

    private $fake = false;

    /**
     * Get the company associated with the user.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get managedcompanies.
     */
    public function managedcompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'manage_user_id');
    }


    /**
     * Get the user's auth token.
     *
     * @return string
     */
    public function getToken()
    {
        return Crypt::encryptString(strval(time() + 60 * env('APP_AUTH_TIMEOUT', 30)) . '/' . (
            $this->fake ?
            $this->fake->id . '/' . $this->fake->email . '/' . $this->email :
            $this->id . '/' . $this->email
        ));
    }

    /**
     * Define setter for the password field.
     */
    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::needsRehash($password) ? Hash::make($password) : $password;
    }

    /**
     * Find User by account/password.
     *
     * @return mixed
     */
    public static function LoginByPassword($account, $password)
    {
        $today = date("Y-m-d");
        $user = User::where('email', $account)->with('company')->first();
        if ($user !== null && Hash::check($password, $user->password)) {
            $user->last_login_at = $today;
            $user->save();
            return $user;
        }
        return null;
    }

    public function getFakeAttribute() {
        return $this->fake;
    }

    public function fakeAs($fakeEmail) {
        if ($this->role == AuthConsts::ROLE_SYS_ADMIN && !empty($fakeEmail)) {
            $fake = User::where([
                ['role', '<>', AuthConsts::ROLE_SYS_ADMIN],
                ['email', '=', $fakeEmail]
            ])->with('company')->first();
            if ($fake) {
                $fake->fake = $this;
                $fake->append('fake');
                return $fake;
            }
        }
        return $this;
    }

    /**
     * Find User by token.
     *
     * @return mixed
     */
    public static function LoginByToken($token)
    {
        $user = null;
        try {
            $decrypted = explode('/', Crypt::decryptString($token));
            if (count($decrypted) > 2 && intval($decrypted[0]) > time()) {
                $today = date("Y-m-d");
                $user = User::where([
                    ['email', $decrypted[2]],
                    ['id', $decrypted[1]]
                ])->with('company')->first();
                if (count($decrypted)==4) {
                    $user = $user->fakeAs ($decrypted[3]);
                }
            }
        } catch(Exception $e) {}
        return $user;
    }
}
