<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ReflectionObject;
use Symfony\Component\HttpFoundation\Response;

class UseForwardedAssetUrls
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = rtrim($request->getSchemeAndHttpHost(), '/');

        if ($origin !== '') {
            $this->setLocalDiskUrl('public', $origin.'/storage');
            $this->setLocalDiskUrl('basset', $origin.'/storage');
            $this->setLocalDiskUrl('public_basset', $origin);
            Storage::forgetDisk(['public', 'basset', 'public_basset']);
            $this->refreshResolvedBassetDisk();
        }

        return $next($request);
    }

    private function setLocalDiskUrl(string $disk, string $url): void
    {
        if (config("filesystems.disks.{$disk}.driver") !== 'local') {
            return;
        }

        config(["filesystems.disks.{$disk}.url" => $url]);
    }

    private function refreshResolvedBassetDisk(): void
    {
        if (! app()->resolved('basset')) {
            return;
        }

        $basset = app('basset');
        $reflection = new ReflectionObject($basset);

        if (! $reflection->hasProperty('disk')) {
            return;
        }

        $disk = $reflection->getProperty('disk');
        $disk->setValue($basset, Storage::disk(config('backpack.basset.disk')));
    }
}
