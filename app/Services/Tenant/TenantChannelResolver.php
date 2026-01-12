<?php

namespace App\Services\Tenant;

use App\Support\Tenant\TenantRequest;
use Illuminate\Http\Request;
use Webkul\Core\Repositories\ChannelRepository;

class TenantChannelResolver
{
    public function __construct(protected ChannelRepository $channelRepository)
    {
    }

    public function resolveChannelCodeForRequest(Request $request): ?string
    {
        if (TenantRequest::isAdminPath($request)) {
            return null;
        }

        $host = TenantRequest::getHost($request);

        if ($host === '') {
            return null;
        }

        $channel = $this->channelRepository->findOneWhere(['hostname' => $host]);

        return $channel?->code;
    }

    public function getDefaultChannelCode(): ?string
    {
        $first = $this->channelRepository->first();

        return $first?->code;
    }
}
