<?php
    require_once('vendor/autoload.php');
    //namespace Oblady;
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
		
		
		public function add($realPath,$inData) {
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
                if (file_exists($inData)) {
                    $this->data[]=str_replace($realPath,'',$inData);
                    echo "Enregistrement du fichier\n";
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
    $array = Spyc::YAMLLoad('./'.$argv[1].'.yml');
    $cacheManifest->realPath = $array["resources"]["realPath"];
	foreach ($array['pages'] as $page) {
		$html = file_get_html($array['domain'].$page);
		$cacheManifest->addUrl($array['domain'].$page);
		
		foreach($html->find('link') as $element) {
			$cacheManifest->cssObj->add($cacheManifest->realPath, $element->href);
		}
		
		// Find all link
		foreach($html->find('script') as $element) {
			$cacheManifest->scriptObj->add($cacheManifest->realPath,$element->src);
		}
	
	}

    foreach ($array["resources"]["paths"] as $path) {
        $finder = new Finder();
        $finder->in($array["resources"]["realPath"].$path);
        foreach ($finder as $finderFile) {
            //var_dump($finderFile);
            $cacheManifest->files->add($cacheManifest->realPath,$finderFile->getRealPath());
        }
    }

	foreach ($array['sql'] as $sql) {
		$cacheManifest->sqlObj->add($cacheManifest->realPath,$sql);
	}
	$cacheManifest->generate();
    if (substr($array["manifest_path"],-1) != "/") $array["manifest_path"].= "/"; //Permet de compléter le dossier de destination s'il manque un /
	file_put_contents($array["manifest_path"].$array["manifest_name"],$cacheManifest->getFile());
?>
