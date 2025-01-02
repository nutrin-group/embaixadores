<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Models\Commission;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'social_network',
        'social_media_username',
        'password',
        'type',
        'balance',
        'manager_referral',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'decimal:2',
        'status' => 'string'
    ];

    // User type constants
    const TYPE_ADMIN = 'admin';
    const TYPE_MANAGER = 'manager';
    const TYPE_AMBASSADOR = 'ambassador';

    // Helper methods to check user type
    public function isAdmin(): bool
    {
        return $this->type === self::TYPE_ADMIN;
    }

    public function isManager(): bool
    {
        return $this->type === self::TYPE_MANAGER;
    }

    public function isAmbassador(): bool
    {
        return $this->type === self::TYPE_AMBASSADOR;
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is pending approval
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if user is blocked
     */
    public function isBlocked(): bool
    {
        return $this->status === 'blocked';
    }

    /**
     * Check if user is refused
     */
    public function isRefused(): bool
    {
        return $this->status === 'refused';
    }

    /**
     * Activate the user
     */
    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Block the user
     */
    public function block(): void
    {
        $this->update(['status' => 'blocked']);
    }

    /**
     * Refuse the user
     */
    public function refuse(): void
    {
        $this->update(['status' => 'refused']);
    }

    /**
     * Get the orders for the user.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the comissions for the user.
     */
    public function comissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * Get the managed comissions for the user.
     */
    public function managedComissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'manager_id');
    }

    /**
     * Get the promocodes for the user.
     */
    public function promocodes(): HasMany
    {
        return $this->hasMany(Promocode::class);
    }

    /**
     * Get the managed promocodes for the user.
     */
    public function managedPromocodes(): HasMany
    {
        return $this->hasMany(Promocode::class, 'manager_id');
    }

    /**
     * Get the payment key for the user.
     */
    public function paymentKey(): HasOne
    {
        return $this->hasOne(PaymentKey::class);
    }

    /**
     * Get the manager for the user.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_referral');
    }

    /**
     * Get the referrals for the user.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'manager_referral');
    }

    /**
     * Get the withdrawals for the user.
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function sales()
    {
        return $this->hasMany(Order::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function ambassadors()
    {
        return $this->hasMany(User::class, 'manager_referral')
            ->where('type', 'ambassador');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(AmbassadorPost::class);
    }

    public function coins()
    {
        return $this->hasMany(Coin::class);
    }

    public function getCoinBalanceAttribute()
    {
        return $this->coins()->sum('amount');
    }

    public function getRankingPositionAttribute()
    {
        if ($this->type !== 'ambassador') {
            return null;
        }

        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        // Subquery para calcular o total de vendas por embaixador no mês atual
        $rankingQuery = DB::table('users as u')
            ->join('orders as o', 'o.user_id', '=', 'u.id')
            ->where('u.type', 'ambassador')
            ->whereBetween('o.created_at', [$startOfMonth, $endOfMonth])
            ->select(
                'u.id',
                DB::raw('COALESCE(SUM(o.amount), 0) as total_sales'),
                DB::raw('DENSE_RANK() OVER (ORDER BY COALESCE(SUM(o.amount), 0) DESC) as ranking')
            )
            ->groupBy('u.id');

        // Busca a posição do usuário atual
        $userRanking = DB::table(DB::raw("({$rankingQuery->toSql()}) as ranking_table"))
            ->mergeBindings($rankingQuery)
            ->where('id', $this->id)
            ->value('ranking');

        // Se não tiver ranking (sem vendas), coloca na última posição
        if (!$userRanking) {
            $totalAmbassadors = $this->getTotalAmbassadorsAttribute();
            return $totalAmbassadors;
        }

        return $userRanking;
    }

    public function getTotalAmbassadorsAttribute()
    {
        return User::where('type', 'ambassador')
                  ->where('status', 'active')
                  ->count();
    }

    public function ambassadorProfile()
    {
        return $this->hasOne(AmbassadorProfile::class);
    }

    // Constante para as redes sociais permitidas
    public const SOCIAL_NETWORKS = [
        'instagram',
        'tiktok',
        'kwai',
        'x',
        'facebook'
    ];

    public function coinBalance()
    {
        return $this->coins()->sum('amount');
    }
}
