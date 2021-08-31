<?php
namespace app\controller;

use app\BaseController;
use think\facade\View;
use think\facade\Request;
use think\facade\Db;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Alignment\LabelAlignmentCenter;
use Endroid\QrCode\Label\Font\NotoSans;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class Invite extends BaseController
{
	public function __construct(\think\App $app){
		parent::__construct($app, false);
	}
	
    public function invite()
    {
		$result = Builder::create()
		    ->writer(new PngWriter())
		    ->writerOptions([])
		    ->data('Custom QR code contents')
		    ->encoding(new Encoding('UTF-8'))
		    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
		    ->size(300)
		    ->margin(10)
		    ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
		    ->logoPath(__DIR__.'/assets/symfony.png')
		    ->labelText('This is the label')
		    ->labelFont(new NotoSans(20))
		    ->labelAlignment(new LabelAlignmentCenter())
		    ->build();
			
        return View::fetch('invite');
    }

    
   
}
