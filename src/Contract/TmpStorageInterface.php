<?php

declare(strict_types=1);

namespace Zjk\TmpStorage\Contract;

interface TmpStorageInterface
{
    public function storage(object $tmp, int $ttl = 604800): string;

    public function fetch(string $id, bool $remove = true): object;

    public function remove(string $id): void;

    public function clearGarbage(): void;
}
