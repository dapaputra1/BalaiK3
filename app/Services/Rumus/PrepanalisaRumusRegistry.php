<?php

namespace App\Services\Rumus;

use App\Models\ServiceParameter;
use App\Services\Rumus\Ambien\DebuService;
use App\Services\Rumus\Ambien\BenzeneService;
use App\Services\Rumus\Ambien\CdService;
use App\Services\Rumus\Ambien\AsService;
use App\Services\Rumus\Ambien\CrService;
use App\Services\Rumus\Ambien\CuService;
use App\Services\Rumus\Ambien\CoService;
use App\Services\Rumus\Ambien\SbService;
use App\Services\Rumus\Ambien\TlService;
use App\Services\Rumus\Ambien\H2sService;
use App\Services\Rumus\Ambien\HgAasService;
use App\Services\Rumus\Ambien\Nh3Service;
use App\Services\Rumus\Ambien\No2Service;
use App\Services\Rumus\Ambien\OxService;
use App\Services\Rumus\Ambien\PbService;
use App\Services\Rumus\Ambien\So2Service;
use App\Services\Rumus\Ambien\TolueneService;
use App\Services\Rumus\Ambien\XyleneService;
use App\Services\Rumus\Ambien\ZnService;
use App\Services\Rumus\Common\PassThroughRumusService;
use App\Services\Rumus\Contracts\PrepanalisaRumusServiceInterface;
use App\Services\Rumus\Emisi\EmisiHclService;
use App\Services\Rumus\Emisi\EmisiHfService;
use App\Services\Rumus\Emisi\EmisiHgService;
use App\Services\Rumus\Emisi\EmisiSo2Service;
use App\Support\ServiceSystemCode;

class PrepanalisaRumusRegistry
{
    private static array $contextCache = [];

    public static function apply(int $serviceParameterId, array $payload): array
    {
        $context = self::resolveContext($serviceParameterId);
        $service = self::resolve($context['system_code'] ?? null);

        return $service->apply(array_merge($payload, [
            'service_parameter_id' => $serviceParameterId,
            'service_parameter_system_code' => $context['system_code'] ?? null,
            'service_category_system_code' => $context['category_system_code'] ?? null,
            'service_parameter_name' => $context['name'] ?? null,
        ]));
    }

    private static function resolve(?string $systemCode): PrepanalisaRumusServiceInterface
    {
        $class = self::serviceMap()[$systemCode] ?? PassThroughRumusService::class;
        $instance = app($class);

        if (!$instance instanceof PrepanalisaRumusServiceInterface) {
            return app(PassThroughRumusService::class);
        }

        return $instance;
    }

    private static function resolveContext(int $serviceParameterId): array
    {
        if (isset(self::$contextCache[$serviceParameterId])) {
            return self::$contextCache[$serviceParameterId];
        }

        $parameter = ServiceParameter::query()
            ->with('category')
            ->find($serviceParameterId);

        self::$contextCache[$serviceParameterId] = [
            'system_code' => $parameter?->system_code,
            'category_system_code' => $parameter?->category?->system_code,
            'name' => $parameter?->name,
        ];

        return self::$contextCache[$serviceParameterId];
    }

    private static function serviceMap(): array
    {
        return [
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'NO2') => No2Service::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'NO21') => No2Service::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'OX') => OxService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'OX1') => OxService::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'NH3') => Nh3Service::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'NH31') => Nh3Service::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'H2S') => H2sService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'H2S1') => H2sService::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'HC') => BenzeneService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'BENZ') => BenzeneService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'HC1') => BenzeneService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'BENZ1') => BenzeneService::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'TOLU') => TolueneService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'TOLU1') => TolueneService::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'XELE') => XyleneService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'XELE1') => XyleneService::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'DPM10') => DebuService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'DPM25') => DebuService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDTLR') => DebuService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'DPM11') => DebuService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'DPM21') => DebuService::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLPB') => PbService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLCD') => CdService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLHG') => HgAasService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLAS') => AsService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLCO') => CoService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLSB') => SbService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLTL') => TlService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLCR') => CrService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLCU') => CuService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'KDLZN') => ZnService::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_LK, 'SO2') => So2Service::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_AMB, 'SO21') => So2Service::class,

            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_EMS, 'SO22') => EmisiSo2Service::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_EMS, 'HF') => EmisiHfService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_EMS, 'HCL') => EmisiHclService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_EMS, 'HCL1') => EmisiHclService::class,
            ServiceSystemCode::parameter(ServiceSystemCode::CATEGORY_EMS, 'HG') => EmisiHgService::class,
        ];
    }
}
