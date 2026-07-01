<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Represents "whose cart/wishlist this is" — either a real authenticated
 * user, or an anonymous guest identified by a cookie token. Centralises the
 * user-vs-guest branch so CartController, WishlistController, and
 * CartService all resolve ownership the same way instead of each
 * re-implementing an Auth::check() branch.
 */
final class CartOwner
{
    private function __construct(
        public readonly ?int $userId,
        public readonly ?string $guestToken,
    ) {
    }

    public static function forUser(User $user): self
    {
        return new self($user->id, null);
    }

    /** $token may be null — a guest who has no cart/wishlist cookie yet. */
    public static function forGuestToken(?string $token): self
    {
        return new self(null, $token);
    }

    public function isGuest(): bool
    {
        return $this->userId === null;
    }

    /** A guest with no cookie yet — nothing to query, an owner but no rows can exist. */
    public function isEmpty(): bool
    {
        return $this->userId === null && $this->guestToken === null;
    }

    public function scope(Builder $query): Builder
    {
        return $this->userId
            ? $query->where('user_id', $this->userId)
            : $query->where('guest_token', $this->guestToken);
    }

    public function attributes(): array
    {
        return $this->userId
            ? ['user_id' => $this->userId, 'guest_token' => null]
            : ['guest_token' => $this->guestToken, 'user_id' => null];
    }
}
