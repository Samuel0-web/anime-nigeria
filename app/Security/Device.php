<?php
namespace App\Security;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;

class Device {
    public static function detect(?string $userAgent = null): array {
        $userAgent ??= $_SERVER['HTTP_USER_AGENT'] ?? '';

        $clientHints = ClientHints::factory([
            'sec-ch-ua'                  => $_SERVER['HTTP_SEC_CH_UA'] ?? '',
            'sec-ch-ua-mobile'           => $_SERVER['HTTP_SEC_CH_UA_MOBILE'] ?? '',
            'sec-ch-ua-model'            => $_SERVER['HTTP_SEC_CH_UA_MODEL'] ?? '',
            'sec-ch-ua-platform'         => $_SERVER['HTTP_SEC_CH_UA_PLATFORM'] ?? '',
            'sec-ch-ua-platform-version' => $_SERVER['HTTP_SEC_CH_UA_PLATFORM_VERSION'] ?? '',
            'sec-ch-ua-full-version'     => $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION'] ?? '',
            'sec-ch-ua-full-version-list'=> $_SERVER['HTTP_SEC_CH_UA_FULL_VERSION_LIST'] ?? '',
            'sec-ch-ua-arch'             => $_SERVER['HTTP_SEC_CH_UA_ARCH'] ?? '',
            'sec-ch-ua-bitness'          => $_SERVER['HTTP_SEC_CH_UA_BITNESS'] ?? '',
            'sec-ch-ua-form-factors'     => $_SERVER['HTTP_SEC_CH_UA_FORM_FACTORS'] ?? '',
        ]);

        $detector = new DeviceDetector($userAgent);
        $detector->setClientHints($clientHints);
        $detector->parse();

        return [
            'device_type'     => $detector->getDeviceName(),
            'brand'           => $detector->getBrandName(),
            'model'           => $detector->getModel(),
            'os'              => $detector->getOs('name'),
            'os_version'      => $detector->getOs('version'),
            'browser'         => $detector->getClient('name'),
            'browser_version' => $detector->getClient('version'),
            'is_bot'          => $detector->isBot(),
        ];
    }
}