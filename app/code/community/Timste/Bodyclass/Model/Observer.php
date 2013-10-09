<?php

/**
 *
 */
class Timste_Bodyclass_Model_Observer
{
    /**
     * @var string
     */
    protected $_useragent = '';

    const CACHE_GROUP = 'block_html';
    const CACHE_KEY = 'timste_bodyclass_useragent';

    /**
     * Operating systems (check Windows CE before Windows and Android before Linux!)
     *
     * @var array
     */
    public $timsteOs = array(
        'Macintosh'     => array('os' => 'mac',        'mobile' => false),
        'Windows CE'    => array('os' => 'win-ce',     'mobile' => true),
        'Windows Phone' => array('os' => 'win-ce',     'mobile' => true),
        'Windows'       => array('os' => 'win',        'mobile' => false),
        'iPad'          => array('os' => 'ios',        'mobile' => false),
        'iPhone'        => array('os' => 'ios',        'mobile' => true),
        'iPod'          => array('os' => 'ios',        'mobile' => true),
        'Android'       => array('os' => 'android',    'mobile' => true),
        'BB10'          => array('os' => 'blackberry', 'mobile' => true),
        'Blackberry'    => array('os' => 'blackberry', 'mobile' => true),
        'Symbian'       => array('os' => 'symbian',    'mobile' => true),
        'WebOS'         => array('os' => 'webos',      'mobile' => true),
        'Linux'         => array('os' => 'unix',       'mobile' => false),
        'FreeBSD'       => array('os' => 'unix',       'mobile' => false),
        'OpenBSD'       => array('os' => 'unix',       'mobile' => false),
        'NetBSD'        => array('os' => 'unix',       'mobile' => false),
    );

    /**
     * Browsers (check OmniWeb and Silk before Safari and Opera Mini/Mobi before Opera!)
     *
     * @var array
     */
    public $timsteBrowser = array (
        'MSIE'       => array(
            'browser'=>'ie',
            'shorty'=>'ie',
            'engine'=>'trident',
            'version'=>'/^.*?MSIE (\d+(\.\d+)*).*$/',
        ),
        'Firefox'    => array(
            'browser'=>'firefox',
            'shorty'=>'fx',
            'engine'=>'gecko',
            'version'=>'/^.*Firefox\/(\d+(\.\d+)*).*$/',
        ),
        'Chrome'     => array(
            'browser'=>'chrome',
            'shorty'=>'ch',
            'engine'=>'webkit',
            'version'=>'/^.*Chrome\/(\d+(\.\d+)*).*$/',
        ),
        'OmniWeb'    => array(
            'browser'=>'omniweb',
            'shorty'=>'ow',
            'engine'=>'webkit',
            'version'=>'/^.*Version\/(\d+(\.\d+)*).*$/',
        ),
        'Silk'       => array(
            'browser'=>'silk',
            'shorty'=>'si',
            'engine'=>'silk',
            'version'=>'/^.*Silk\/(\d+(\.\d+)*).*$/',
        ),
        'Safari'     => array(
            'browser'=>'safari',
            'shorty'=>'sf',
            'engine'=>'webkit',
            'version'=>'/^.*Version\/(\d+(\.\d+)*).*$/',
        ),
        'Opera Mini' => array(
            'browser'=>'opera-mini',
            'shorty'=>'oi',
            'engine'=>'presto',
            'version'=>'/^.*Opera Mini\/(\d+(\.\d+)*).*$/',
        ),
        'Opera Mobi' => array(
            'browser'=>'opera-mobile',
            'shorty'=>'om',
            'engine'=>'presto',
            'version'=>'/^.*Version\/(\d+(\.\d+)*).*$/',
        ),
        'Opera'      => array(
            'browser'=>'opera',
            'shorty'=>'op',
            'engine'=>'presto',
            'version'=>'/^.*Version\/(\d+(\.\d+)*).*$/',
        ),
        'IEMobile'   => array(
            'browser'=>'ie-mobile',
            'shorty'=>'im',
            'engine'=>'trident',
            'version'=>'/^.*IEMobile (\d+(\.\d+)*).*$/',
        ),
        'Camino'     => array(
            'browser'=>'camino',
            'shorty'=>'ca',
            'engine'=>'gecko',
            'version'=>'/^.*Camino\/(\d+(\.\d+)*).*$/',
        ),
        'Konqueror'  => array(
            'browser'=>'konqueror',
            'shorty'=>'ko',
            'engine'=>'webkit',
            'version'=>'/^.*Konqueror\/(\d+(\.\d+)*).*$/',
        )
    );


    /**
     * add CSS classes for different browsers and stores to HTML body
     *
     * @param Varien_Event_Observer $observer - observer out of event
     *                              core_block_abstract_to_html_before
     *
     * @return Timste_Bodyclass_Model_Observer
     */
    public function addTimsteBodyclass(Varien_Event_Observer $observer)
    {
        $block = $observer->getLayout()->getBlock('root');

        if ($block) {
            $result = $this->getUserAgent();

            $block->addBodyClass($result['os']);
            $block->addBodyClass($result['browser']);
            $block->addBodyClass('v'.$result['version']);
            $block->addBodyClass($result['engine']);

            /* add the store code */
            try {
                $store_code = Mage::app()->getStore()->getCode();
                $block->addBodyClass($store_code);
            } catch( Exception $e ) {
                // if error happens, no store is available as CSS class
            }
        }

        return $this;
    }


    /**
     * @return array
     */
    public function getUserAgent()
    {
        $ua = 'unknown';
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }
        $cache = Mage::app()->loadCache(self::CACHE_KEY);
        if (is_array($cache) && isset($cache[$ua])) {
            return $cache[$ua];
        } else if (!is_array($cache)) {
            $cache = array();
        }

        $return = array(
            'string'    => $ua,
        );

        $os = 'unknown';
        $mobile = false;
        $browser = 'other';
        $shorty = '';
        $version = '';
        $engine = '';

        // Operating system
        foreach ($this->timsteOs as $k=>$v) {
            if (stripos($ua, $k) !== false) {
                $os = $v['os'];
                $mobile = $v['mobile'];
                break;
            }
        }

        $return['os'] = $os;

        // Browser and version
        foreach ($this->timsteBrowser as $k => $v) {
            if (stripos($ua, $k) !== false) {
                $browser = $v['browser'];
                $shorty  = $v['shorty'];
                $version = preg_replace($v['version'], '$1', $ua);
                $engine  = $v['engine'];
                break;
            }
        }

        $versions = explode('.', $version);
        $version  = $versions[0];

        $return['class'] = $os . ' ' . $browser . ' ' . $engine;

        // Add the version number if available
        if ($version != '') {
            $return['class'] .= ' ' . $shorty . $version;
        }

        // Mark mobile devices
        if ($mobile) {
            $return['class'] .= ' mobile';
        }

        // Android tablets are not mobile
        if (($os == 'Android') && (stripos('mobile', $ua) === false)) {
            $mobile = false;
        }

        $return['browser']  = $browser;
        $return['shorty']   = $shorty;
        $return['version']  = $version;
        $return['engine']   = $engine;
        $return['versions'] = $versions;
        $return['mobile']   = $mobile;

        $cache[$ua] = $return;
        Mage::app()->saveCache($cache, self::CACHE_KEY, array(self::CACHE_GROUP));

        return $return;
    }
}
