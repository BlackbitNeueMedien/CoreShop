<?php
/**
 * CoreShop.
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2020 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

declare(strict_types=1);

namespace CoreShop\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use CoreShop\Behat\Service\SharedStorageInterface;
use CoreShop\Component\Address\Context\FixedCountryContext;
use CoreShop\Component\Address\Model\ZoneInterface;
use CoreShop\Component\Core\Model\CountryInterface;
use CoreShop\Component\Core\Model\CurrencyInterface;
use CoreShop\Component\Core\Model\StoreInterface;
use CoreShop\Component\Core\Repository\CountryRepositoryInterface;
use CoreShop\Component\Resource\Factory\FactoryInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Process\Process;

final class CountryContext implements Context
{
    private $sharedStorage;
    private $objectManager;
    private $countryFactory;
    private $countryRepository;
    private $fixedCountryContext;
    private $kernelRootDirectory;

    public function __construct(
        SharedStorageInterface $sharedStorage,
        ObjectManager $objectManager,
        FactoryInterface $countryFactory,
        CountryRepositoryInterface $countryRepository,
        FixedCountryContext $fixedCountryContext,
        string $kernelRootDirectory
    ) {
        $this->sharedStorage = $sharedStorage;
        $this->objectManager = $objectManager;
        $this->countryFactory = $countryFactory;
        $this->countryRepository = $countryRepository;
        $this->fixedCountryContext = $fixedCountryContext;
        $this->kernelRootDirectory = $kernelRootDirectory;
    }

    /**
     * @Given /^the (country "[^"]+") is valid for (store "[^"]+")$/
     * @Given /^the (country) is valid for (store "[^"]+")$/
     */
    public function currencyIsValidForStore(CountryInterface $country, StoreInterface $store)
    {
        $store->addCountry($country);

        $this->objectManager->persist($store);
        $this->objectManager->flush();
    }

    /**
     * @Given /^the (country "[^"]+") is invalid for (store "[^"]+")$/
     */
    public function currencyIsInValidForStore(CountryInterface $country, StoreInterface $store)
    {
        $store->removeCountry($country);

        $this->objectManager->persist($store);
        $this->objectManager->flush();
    }

    /**
     * @Given /^the site has a country "([^"]+)" with (currency "[^"]+")$/
     */
    public function theSiteHasACountry($name, CurrencyInterface $currency)
    {
        $this->createCountry($name, $currency);
    }

    /**
     * @Then /^the (country "[^"]+") is in (zone "[^"]+")$/
     */
    public function theCountryIsInZone(CountryInterface $country, ZoneInterface $zone)
    {
        $country->setZone($zone);

        $this->saveCountry($country);
    }

    /**
     * @Then /^the (country "[^"]+") is active$/
     */
    public function theCountryIsActive(CountryInterface $country)
    {
        $country->setActive(true);

        $this->saveCountry($country);
    }

    /**
     * @Given /^I am in (country "[^"]+")$/
     */
    public function iAmInCountry(CountryInterface $country)
    {
        $this->fixedCountryContext->setCountry($country);
    }

    /**
     * @Given /^the (countries) address format is "(.*)"$/
     * @Given /^the (countries "[^"]+") address format is "(.*)"$/
     */
    public function theCountriesAddressFormatIs(CountryInterface $country, $format)
    {
        $country->setAddressFormat(str_replace("'", '"', $format));

        $this->saveCountry($country);
    }

    /**
     * @Given /^I downloaded the GeoLite2 DB$/
     */
    public function iDownloadedTheGeoLite2DB()
    {
        $process = new Process(
            [
                sprintf('%s/etc/geoipupdate/geoipupdate', $this->kernelRootDirectory),
                '-f',
                sprintf('%s/etc/geoipupdate/GeoIP.conf', $this->kernelRootDirectory),
                '-d',
                sprintf('%s/var/config/', $this->kernelRootDirectory)
            ]
        );
        $process->run();
    }

    /**
     * @param string $name
     */
    private function createCountry($name, CurrencyInterface $currency)
    {
        $country = $this->countryRepository->findByName($name, 'en');

        if (!$country) {
            /**
             * @var CountryInterface $country
             */
            $country = $this->countryFactory->createNew();
            $country->setName($name, 'en');
            $country->setIsoCode($name);
            $country->setActive(true);
            $country->setCurrency($currency);

            $this->saveCountry($country);
        }
    }

    /**
     * @param CountryInterface $country
     */
    private function saveCountry(CountryInterface $country)
    {
        $this->objectManager->persist($country);
        $this->objectManager->flush();

        $this->sharedStorage->set('country', $country);
    }
}
