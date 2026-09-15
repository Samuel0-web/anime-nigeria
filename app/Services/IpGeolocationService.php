<?php
namespace App\Services;

use App\Core\Logger;
use GuzzleHttp\ClientInterface;

/**
 * Resolves an approximate, city-level location from a public IP address.
 *
 * This is supplementary session metadata only, never an authentication
 * requirement. Every failure path -- an unroutable IP, a network error,
 * a timeout, a non-200 response, a provider-reported error, or a
 * malformed payload -- returns null rather than throwing, so a caller
 * can never have its own logic broken by this service.
 *
 * The raw provider response is never returned to callers; only the
 * fields the application actually uses are exposed, under the
 * application's own naming.
 */
class IpGeolocationService {
    private const ENDPOINT = 'https://ipapi.co/%s/json/';
    private const CONNECT_TIMEOUT = 2.0;
    private const TIMEOUT = 3.0;

    private ClientInterface $client;

    public function __construct(ClientInterface $client) {
        $this->client = $client;
    }

    /**
     * Look up the approximate location for an IP address.
     *
     * Returns null if the address is not publicly geolocatable, or if
     * the lookup fails for any reason.
     *
     * @return array{city: ?string, region: ?string, country: ?string, country_code: ?string}|null
     */
    public function lookup(?string $ip): ?array {
        if (!$this->isPubliclyRoutable($ip)) {
            return null;
        }

        try {
            $response = $this->client->request('GET', sprintf(self::ENDPOINT, $ip), [
                'connect_timeout' => self::CONNECT_TIMEOUT,
                'timeout' => self::TIMEOUT,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);

            if ($response->getStatusCode() !== 200) {
                return null;
            }

            $data = json_decode((string) $response->getBody(), true);

            if (!is_array($data)) {
                return null;
            }

            return $this->normalize($data);
        } catch (\Throwable $e) {
            Logger::error($e);
            return null;
        }
    }

    /**
     * Reject anything that isn't a publicly routable address -- private,
     * reserved, loopback, and malformed addresses are all excluded so
     * we never send them to the external service (also covers local
     * development addresses such as 127.0.0.1 or ::1).
     */
    private function isPubliclyRoutable(?string $ip): bool {
        if ($ip === null || $ip === '') {
            return false;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    /**
     * Normalize ipapi.co's response shape into the application's own
     * field names. A provider-reported error, or a response with no
     * usable fields at all, is treated the same as "no location".
     */
    private function normalize(array $data): ?array {
        if (!empty($data['error'])) {
            return null;
        }

        $city = $this->cleanString($data['city'] ?? null);
        $region = $this->cleanString($data['region'] ?? null);
        $country = $this->cleanString($data['country_name'] ?? null);
        $countryCode = $this->cleanString($data['country_code'] ?? null);

        if ($countryCode !== null) {
            $countryCode = strtoupper(substr($countryCode, 0, 2));
        }

        if ($city === null && $region === null && $country === null && $countryCode === null) {
            return null;
        }

        return [
            'city' => $city,
            'region' => $region,
            'country' => $country,
            'country_code' => $countryCode,
        ];
    }

    private function cleanString(mixed $value): ?string {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        return $value === '' ? null : $value;
    }
}