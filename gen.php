<?php
//namespace Oblady;
require_once(__DIR__.'/../../autoload.php');

include("vendor/bin/simple_html_dom.php");
include('spyc-0.5/spyc.php');

use Symfony\Component\Finder\Finder;




class cacheManifest {

    private $cacheManifest;
    public $cssObj;
    public $scriptObj;
    public $sqlObj;
    public $files;
    private $urlTab;
    public $realPath;

    public function __construct() {
        $this->cacheManifest = "";
        $this->urlTab=[];
        $this->cssObj= new Element();
        $this->scriptObj=new Element();
        $this->sqlObj=new Element();
        $this->files=new Element();
    }

    public function addUrl($url) {
        $this->urlTab[]=$url;
    }

    private function addCommentLine($comment) {
        $this->cacheManifest .= PHP_EOL.'# '.$comment.PHP_EOL.PHP_EOL;
    }
    private function addLine($line) {
        $this->cacheManifest .= $line.PHP_EOL;
    }
    private function addBlankLine() {
        $this->cacheManifest .= PHP_EOL;
    }



    public function generate() {
        //Génération de l'en-tête de Cache Manifest

        $this->addLine('CACHE MANIFEST');
        $this->addCommentLine('Generated on '.time().' ('.date('d/m/Y, G:i:s').')');
        $this->addLine('CACHE:');
        $this->addCommentLine('Static pages');

        foreach ($this->urlTab as $url) {
            $this->addLine($url);
        }

        $this->addCommentLine('Static resources');
        foreach ($this->cssObj->getData() as $obj) {
            $this->addLine($obj);
        }

        foreach ($this->scriptObj->getData() as $obj) {
            $this->addLine($obj);
        }

        $this->addCommentLine('Uploaded files');

        //var_dump($this->files);die;
        foreach ($this->files->getData() as $uploadedFile) {
            $this->addLine($uploadedFile);
        }

        $this->addBlankLine();
        $this->addLine('NETWORK:');
        $this->addLine('*');
    }

    public function getFile() {
        return $this->cacheManifest;
    }
}



class Element {
    private $data = [];

    /**
     * @param $realPath
     * @param $inData
     */
    public function add($realPath,$inData,$type) {
        //var_dump("data:",$this->data);
        //var_dump($inData);
        $exist = false;
        foreach ($this->data as $data) {
            //Vérification que le fichier a pas déjà été traité
            if ($inData == $data) {
                //echo $inData."\n";
                //vérification de l'exsitence du fichier
                $exist=true;
                break;
            }
        }
        if (!$exist) {
            //Vérification de l'existence du fichier
            if (file_exists($inData) && $type=='int') {
                $this->data[]=str_replace($realPath,'',$inData);
                //echo "Enregistrement du fichier\n";
            }
            if ($type=='ext') {
                $url = str_replace($realPath,'',$inData);
                //Vérification que le fichier existe
                if (file_get_contents($url)) $this->data[]=$url;
            }
        }
    }

    public function getData(){
        return $this->data;
    }
}

/**
 * Début du moteur
 */

if (!array_key_exists(1,$argv)) die("\033[31;1;5mPas de ficher init.yml\033[0m\n");
$cacheManifest = new cacheManifest();
if (!file_exists('./'.$argv[1].'.yml')) die("\033[31;1;5mLe fichier d'init  ./".$argv[1].".yml inconnu\033[0m\n");
$array = Spyc::YAMLLoad('./'.$argv[1].'.yml');

$cacheManifest->realPath = $array["resources"]["realPath"];
foreach ($array['pages'] as $page) {
    $html = file_get_html($array['domain'].$page);
    $cacheManifest->addUrl($array['domain'].$page);

    foreach($html->find('link') as $element) {
        $cacheManifest->cssObj->add($cacheManifest->realPath, $element->href,'int');
    }

    // Find all link
    foreach($html->find('script') as $element) {
        $cacheManifest->scriptObj->add($cacheManifest->realPath,$element->src,'int');
    }

}

foreach ($array["resources"]["paths"] as $path) {
    $finder = new Finder();
    $finder->in($array["resources"]["realPath"].$path);
    foreach ($finder->files() as $finderFile) {
        $cacheManifest->files->add($cacheManifest->realPath,$finderFile->getRealPath(),'int');
    }
}


//var_dump($array['api']);

foreach ($array['api'] as $api) {

    $records = file_get_contents($api['url']);
    $cacheManifest->addUrl($api['url']);
    if (array_key_exists('field',$api)) {
        foreach (json_decode($records) as $record) {
            //echo $api['subUrl'].$record->$api['field'];die;
            $subRecord=json_decode(file_get_contents($api['subUrl'].$record->$api['field']));
            //var_dump($subRecord);
            foreach ($subRecord->coloris as $colori) {
                foreach ($api['subFields'] as $subField) {

                    if ($colori->$subField) {
                        echo $api['subUrlImages'] . $colori->$subField . "\n";
                        $cacheManifest->files->add($cacheManifest->realPath, $api['subUrlImages'] . $colori->$subField,'ext');
                    }
                }
            }
        }
    }

}
//$cacheManifest->sqlObj->add($cacheManifest->realPath,$sql);



$cacheManifest->generate();
if (substr($array["manifest_path"],-1) != "/") $array["manifest_path"].= "/"; //Permet de compléter le dossier de destination s'il manque un /
file_put_contents($array["manifest_path"].$array["manifest_name"],$cacheManifest->getFile());
?>
