<?php
/**
 * Copyright © PHP Digital, Inc. All rights reserved.
 */
namespace AlbertMage\Directory\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Directory\Model\RegionFactory;
use AlbertMage\Directory\Model\CityFactory;
use AlbertMage\Directory\Model\DistrictFactory;

/**
 * A console command that lists all the existing users.
 *
 * To use this command, open a terminal window, enter into your project directory
 * and execute the following:
 *
 *     $ php bin/console app:list-users
 *
 * Check out the code of the src/Command/AddUserCommand.php file for
 * the full explanation about Symfony commands.
 *
 * See https://symfony.com/doc/current/console.html
 *
 * @author Albert Shen <albertshen1206@gmail.com>
 */
class ImportWeChatRegionCommand extends Command
{

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var RegionFactory
     */
    protected $regionFactory;

    /**
     * @var CityFactory
     */
    protected $cityFactory;

    /**
     * @var DistrictFactory
     */
    protected $districtFactory;

    /**
     * @var regionList
     */
    protected $regionList;

    /**
     * @var RegionCollectionFactory
     */
    protected $regionCollectionFactory;

    public function __construct(
        \Magento\Framework\App\State $appState,
        RegionFactory $regionFactory,
        CityFactory $cityFactory,
        DistrictFactory $districtFactory,
        RegionCollectionFactory $regionCollectionFactory
    ) {

        $this->appState = $appState;
        $this->regionFactory = $regionFactory;
        $this->cityFactory = $cityFactory;
        $this->districtFactory = $districtFactory;
        $this->regionCollectionFactory = $regionCollectionFactory;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('directory:wechat:region')
            ->setDescription('Import WeChat region');
        parent::configure();
    }

    /**
     * This method is executed after initialize(). It usually contains the logic
     * to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // $string = '230100';
        // var_dump(strpos($string, ''));exit;
        // preg_match_all('/0*$/', $string, $matches);
        // var_dump(strlen($matches[0][0]));
        // exit;
        // $a = 2;
        // $preg = "/0{".$a."}$/";
        // $a = preg_match($preg, $string);
        // if (preg_match($preg, $string) && !preg_match('/00$/', preg_replace($preg, '', $string))) {
        //     var_dump('ok');
        // } else {
        //     var_dump('not ok');
        // }

        $regionCollection = $this->regionCollectionFactory->create();
        $regionCollection->addFieldToFilter('country_id', ['eq' => 'CN']);
        foreach ($regionCollection->getItems() as $item) {
            $item->delete();
        }
        $json = file_get_contents('https://apis.map.qq.com/ws/district/v1/list?key=MUNBZ-K27H3-WDR3E-YKJNK-CUQGZ-XJBCM&output=json');
        $this->regionList = json_decode($json, true);

        $this->regionList['result'][1][] = [
            'id' => '110100',
            'fullname' => '北京市'
        ];
        $this->regionList['result'][1][] = [
            'id' => '310100',
            'fullname' => '上海市'
        ];
        $this->regionList['result'][1][] = [
            'id' => '120100',
            'fullname' => '天津市'
        ];
        $this->regionList['result'][1][] = [
            'id' => '500100',
            'fullname' => '重庆市'
        ];
        
        $regions = $this->createRegion();

        foreach ($regions as $region) {
            $province = $this->regionFactory->create();
            $province->setCountryId('CN');
            $province->setCode($region['id']);
            $province->setDefaultName($region['fullname']);
            $province->save();
            foreach ($region['children'] as $pChild) {
                $city = $this->cityFactory->create();
                $city->setRegionId($province->getId());
                $city->setCode($pChild['id']);
                $city->setDefaultName($pChild['fullname']);
                $city->save();
                foreach ($pChild['children'] as $cChild) {
                    $district = $this->districtFactory->create();
                    $district->setCityId($city->getId());
                    $district->setCode($cChild['id']);
                    $district->setDefaultName($cChild['fullname']);
                    $district->save();
                }
            }
        }

        return 1;
    }

    private function createRegion($parentCode = '') {
        if (!$parentCode) {
            $level = 1;
        } else {
            $level = strlen($parentCode)/2 + 1;
        }
        $zeroNum = 6 - $level * 2;
        $region = [];

        foreach ($this->regionList['result'] as $slice) {
            foreach($slice as $item) {
                preg_match_all('/0*$/', $item['id'], $matches);
                if (strlen($matches[0][0]) == $zeroNum && strpos($item['id'], $parentCode) === 0) {
                    $preg = "/0{".$zeroNum."}$/";
                    $code = preg_replace($preg, '', $item['id']);
                    $region[$item['id']] = [
                        'id' => $item['id'],
                        'fullname' => $item['fullname'],
                        'children' => $this->createRegion($code)
                    ];
                }
            }
        }
        return $region;
    }

}
