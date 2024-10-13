<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use App\Enums\AccountProvider;
use App\Models\CouncilPosition;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Cache\Store;
use Filament\Models\Contracts\HasName;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser, HasName
{

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'slug',
        'image',
        'role',
        'email',
        'password',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function councilPositions(){
        return $this->hasMany(CouncilPosition::class);
    }

    public function scopeCouncilPositionYear($query, $councilId){
        return $query->whereHas('councilPositions.council', function($q) use($councilId){
            $q->where('id',$councilId);
        });
    }
    public function getFilamentName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    public function fullName(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }


    public function getImage()
    {
        if (!empty($this->image)) {
            return Storage::disk('public')->url($this->image); // This should return the full URL for the stored image
        }

        // Return the full URL for the placeholder image
        return url('images/placeholder-image.jpg');


    }   

    // this can be use for.. who change the status 

    public function defaultCouncilPosition()
{
    return $this->councilPositions()->where('is_login', true)->first();
}
public function scopeHasPositionInCouncil($query, $councilId)
{
    return $query->whereHas('councilPositions', function($q) use($councilId) {
        $q->where('council_id', $councilId);
    });
}

public function currentCouncilPosition($councilId)
{
    return $this->councilPositions()->where('council_id', $councilId)->first();
}

public function hasMultiplePositionsInCouncil($councilId)
{
    return $this->councilPositions()->where('council_id', $councilId)->count() > 1;
}
public function activeCouncilPositions()
{
    return $this->councilPositions()->where('is_login', true)->get();
}
public function assignPosition($councilId, $position, $isLogin = false)
{
    // Check if the user already has a position in the same council
    if ($this->councilPositions()->where('council_id', $councilId)->exists()) {
        throw new \Exception("User already has a position in this council.");
    }

    // Assign new position
    return $this->councilPositions()->create([
        'council_id' => $councilId,
        'position' => $position,
        'is_login' => $isLogin,
    ]);
}


}
