<?php
class Timste_Bodyclass_Block_Html extends Mage_Page_Block_Html
{
    protected $_useragent = '';

    public function __construct()
    {
        parent::__construct();

        $this->_urls = array(
            'base'      => Mage::getBaseUrl('web'),
            'baseSecure'=> Mage::getBaseUrl('web', true),
            'current'   => $this->getRequest()->getRequestUri()
        );

        $action = Mage::app()->getFrontController()->getAction();
        if ($action) {
            $this->addBodyClass($action->getFullActionName('-'));
        }

        $result = $this->agent();

        $this->addBodyClass( $result['class'] );

        if( $result['mobile'] == 1 ){
            $this->addBodyClass( 'mobile' );
        }



        $this->_beforeCacheUrl();
    }


    public function agent()
    {

        /**
         * Operating systems (check Windows CE before Windows and Android before Linux!)
         */
        $timste['os'] = array
        (
            'Macintosh'     => array('os'=>'mac',        'mobile'=>false),
            'Windows CE'    => array('os'=>'win-ce',     'mobile'=>true),
            'Windows Phone' => array('os'=>'win-ce',     'mobile'=>true),
            'Windows'       => array('os'=>'win',        'mobile'=>false),
            'iPad'          => array('os'=>'ios',        'mobile'=>false),
            'iPhone'        => array('os'=>'ios',        'mobile'=>true),
            'iPod'          => array('os'=>'ios',        'mobile'=>true),
            'Android'       => array('os'=>'android',    'mobile'=>true),
            'BB10'          => array('os'=>'blackberry', 'mobile'=>true),
            'Blackberry'    => array('os'=>'blackberry', 'mobile'=>true),
            'Symbian'       => array('os'=>'symbian',    'mobile'=>true),
            'WebOS'         => array('os'=>'webos',      'mobile'=>true),
            'Linux'         => array('os'=>'unix',       'mobile'=>false),
            'FreeBSD'       => array('os'=>'unix',       'mobile'=>false),
            'OpenBSD'       => array('os'=>'unix',       'mobile'=>false),
            'NetBSD'        => array('os'=>'unix',       'mobile'=>false),
        );


        /**
         * Browsers (check OmniWeb and Silk before Safari and Opera Mini/Mobi before Opera!)
         */
        $timste['browser'] = array
        (
            'MSIE'       => array('browser'=>'ie',           'shorty'=>'ie', 'engine'=>'trident', 'version'=>'/^.*?MSIE (\d+(\.\d+)*).*$/'),
            'Firefox'    => array('browser'=>'firefox',      'shorty'=>'fx', 'engine'=>'gecko',   'version'=>'/^.*Firefox\/(\d+(\.\d+)*).*$/'),
            'Chrome'     => array('browser'=>'chrome',       'shorty'=>'ch', 'engine'=>'webkit',  'version'=>'/^.*Chrome\/(\d+(\.\d+)*).*$/'),
            'OmniWeb'    => array('browser'=>'omniweb',      'shorty'=>'ow', 'engine'=>'webkit',  'version'=>'/^.*Version\/(\d+(\.\d+)*).*$/'),
            'Silk'       => array('browser'=>'silk',         'shorty'=>'si', 'engine'=>'silk',    'version'=>'/^.*Silk\/(\d+(\.\d+)*).*$/'),
            'Safari'     => array('browser'=>'safari',       'shorty'=>'sf', 'engine'=>'webkit',  'version'=>'/^.*Version\/(\d+(\.\d+)*).*$/'),
            'Opera Mini' => array('browser'=>'opera-mini',   'shorty'=>'oi', 'engine'=>'presto',  'version'=>'/^.*Opera Mini\/(\d+(\.\d+)*).*$/'),
            'Opera Mobi' => array('browser'=>'opera-mobile', 'shorty'=>'om', 'engine'=>'presto',  'version'=>'/^.*Version\/(\d+(\.\d+)*).*$/'),
            'Opera'      => array('browser'=>'opera',        'shorty'=>'op', 'engine'=>'presto',  'version'=>'/^.*Version\/(\d+(\.\d+)*).*$/'),
            'IEMobile'   => array('browser'=>'ie-mobile',    'shorty'=>'im', 'engine'=>'trident', 'version'=>'/^.*IEMobile (\d+(\.\d+)*).*$/'),
            'Camino'     => array('browser'=>'camino',       'shorty'=>'ca', 'engine'=>'gecko',   'version'=>'/^.*Camino\/(\d+(\.\d+)*).*$/'),
            'Konqueror'  => array('browser'=>'konqueror',    'shorty'=>'ko', 'engine'=>'webkit',  'version'=>'/^.*Konqueror\/(\d+(\.\d+)*).*$/')
        );

        $ua = $_SERVER['HTTP_USER_AGENT'];

        $return = array();
        $return['string'] = $ua;

        $os = 'unknown';
        $mobile = false;
        $browser = 'other';
        $shorty = '';
        $version = '';
        $engine = '';

        // Operating system
        foreach ($timste['os'] as $k=>$v)
        {
            if (stripos($ua, $k) !== false)
            {
                $os = $v['os'];
                $mobile = $v['mobile'];
                break;
            }
        }

        $return['os'] = $os;

        // Browser and version
        foreach ($timste['browser'] as $k=>$v)
        {
            if (stripos($ua, $k) !== false)
            {
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
        if ($version != '')
        {
            $return['class'] .= ' ' . $shorty . $version;
        }

        // Mark mobile devices
        if ($mobile)
        {
            $return['class'] .= ' mobile';
        }

        // Android tablets are not mobile (see #4150)
        if ($os == 'Android' && stripos('mobile', $ua) === false)
        {
            $mobile = false;
        }

        $return['browser']  = $browser;
        $return['shorty']   = $shorty;
        $return['version']  = $version;
        $return['engine']   = $engine;
        $return['versions'] = $versions;
        $return['mobile']   = $mobile;

        return $return;
    }

}