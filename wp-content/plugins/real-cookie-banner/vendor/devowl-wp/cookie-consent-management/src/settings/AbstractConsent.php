<?php

namespace DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\settings;

use DevOwl\RealCookieBanner\Vendor\DevOwl\CookieConsentManagement\services\Service;
/**
 * Abstract implementation of the settings for misc consent settings (e.g. duration of consent management cookie
 * which saves the decision of the user).
 * @internal
 */
abstract class AbstractConsent extends BaseSettings
{
    /**
     * A list of predefined lists for e.g. `GDPR` considered as secury country for data processing in unsafe countries.
     */
    const PREDEFINED_DATA_PROCESSING_IN_SAFE_COUNTRIES_LISTS = [
        // EU: https://reciprocitylabs.com/resources/what-countries-are-covered-by-gdpr/
        // EEA: https://ec.europa.eu/eurostat/statistics-explained/index.php?title=Glossary:European_Economic_Area_(EEA)
        'GDPR' => ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IS', 'IT', 'LI', 'LV', 'LT', 'LU', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE'],
        'ADEQUACY' => ['AD', 'AR', 'CA', 'FO', 'GG', 'IL', 'IM', 'JP', 'JE', 'NZ', 'KR', 'CH', 'GB', 'UY', 'US'],
    ];
    const AGE_NOTICE_COUNTRY_AGE_MAP = ['INHERIT' => 0, 'GDPR' => 16, 'BE' => 13, 'DK' => 13, 'EE' => 13, 'FI' => 13, 'IS' => 13, 'LV' => 13, 'NO' => 13, 'PT' => 13, 'SE' => 13, 'MT' => 13, 'AT' => 14, 'BG' => 14, 'CY' => 14, 'IT' => 14, 'LT' => 14, 'ES' => 14, 'CZ' => 15, 'FR' => 15, 'GR' => 15, 'SI' => 15, 'DE' => 16, 'HR' => 16, 'HU' => 16, 'LI' => 16, 'LU' => 16, 'NL' => 16, 'PL' => 16, 'RO' => 16, 'SK' => 16, 'IE' => 16, 'CH' => 13];
    /**
     * Search the coding for difference.
     */
    const COOKIE_VERSION_1 = 1;
    const COOKIE_VERSION_2 = 2;
    const COOKIE_VERSION_3 = 3;
    const DEFAULT_COOKIE_VERSION = self::COOKIE_VERSION_3;
    /**
     * Check if bots should acceppt all cookies automatically.
     *
     * @return boolean
     */
    public abstract function isAcceptAllForBots();
    /**
     * Check if "Do not Track" header is respected.
     *
     * @return boolean
     */
    public abstract function isRespectDoNotTrack();
    /**
     * Check if IPs should be saved in plain in database.
     *
     * @return boolean
     */
    public abstract function isSaveIpEnabled();
    /**
     * Check if age notice hint is enabled
     *
     * @return boolean
     */
    public abstract function isAgeNoticeEnabled();
    /**
     * Check if list-services notice hint is enabled
     *
     * @return boolean
     */
    public abstract function isListServicesNoticeEnabled();
    /**
     * Get the cookie duration for the consent cookies.
     *
     * @return int
     */
    public abstract function getCookieDuration();
    /**
     * Get the cookie version for the consent cookies.
     *
     * @return int
     */
    public abstract function getCookieVersion();
    /**
     * Get the consent duration.
     *
     * @return int
     */
    public abstract function getConsentDuration();
    /**
     * Check if data processing in unsafe countries is enabled.
     *
     * @return boolean
     */
    public abstract function isDataProcessingInUnsafeCountries();
    /**
     * Get safe countries for the data-processing-in-unsafe-countries option.
     *
     * @return string[]
     */
    public abstract function getDataProcessingInUnsafeCountriesSafeCountriesRaw();
    /**
     * Get safe countries for the data-processing-in-unsafe-countries option with expanded predefined lists.
     *
     * @param string[]
     */
    public function getDataProcessingInUnsafeCountriesSafeCountries()
    {
        $result = [];
        foreach ($this->getDataProcessingInUnsafeCountriesSafeCountriesRaw() as $code) {
            if (\strlen($code) !== 2) {
                $predefinedList = self::PREDEFINED_DATA_PROCESSING_IN_SAFE_COUNTRIES_LISTS[$code] ?? [];
                $result = \array_merge($result, $predefinedList);
            } else {
                $result[] = $code;
            }
        }
        return $result;
    }
    /**
     * Calculate configured services which are processing data in unsafe countries.
     */
    public function calculateServicesWithDataProcessingInUnsafeCountries()
    {
        $tcf = $this->getSettings()->getTcf();
        $groups = $this->getSettings()->getGeneral()->getServiceGroups();
        $tcfVendorConfigurations = $tcf->getVendorConfigurations();
        $safeCountries = [];
        foreach (self::PREDEFINED_DATA_PROCESSING_IN_SAFE_COUNTRIES_LISTS as $listCountries) {
            $safeCountries = \array_merge($safeCountries, $listCountries);
        }
        $candidates = [];
        foreach ($groups as $group) {
            foreach ($group->getItems() as $service) {
                $unsafeCountries = Service::calculateUnsafeCountries($service->getDataProcessingInCountries(), $service->getDataProcessingInCountriesSpecialTreatments());
                if (\count($unsafeCountries) > 0) {
                    $candidates[] = ['unsafeCountries' => $unsafeCountries, 'name' => $service->getName()];
                }
            }
        }
        if ($tcf->isActive()) {
            $vendorIds = [];
            foreach ($tcfVendorConfigurations as $configuration) {
                $vendorId = $configuration->getVendorId();
                $unsafeCountries = Service::calculateUnsafeCountries($configuration->getDataProcessingInCountries(), $configuration->getDataProcessingInCountriesSpecialTreatments());
                if (\count($unsafeCountries) > 0) {
                    $candidates[] = ['unsafeCountries' => $unsafeCountries, 'name' => $vendorId, 'tcf' => \true];
                    $vendorIds[] = $vendorId;
                }
            }
            // Read TCF vendor names
            if (\count($vendorIds) > 0) {
                $vendors = $tcf->queryVendors(['in' => $vendorIds])['vendors'];
                foreach ($candidates as $key => $candidate) {
                    if (isset($candidate['tcf'])) {
                        $candidates[$key]['name'] = $vendors[$candidate['name']]['name'];
                    }
                }
            }
        }
        return $candidates;
    }
}
