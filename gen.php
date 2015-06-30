<?php
    require_once('vendor/autoload.php');
    //namespace Oblady;
	include("./vendor/bin/simple_html_dom.php");
	include('./spyc-0.5/spyc.php');

    use Symfony\Component\Finder\Finder;




	class cacheManifest {
		
		private $cacheManifest;
		public $cssObj;
		public $scriptObj;
		public $sqlObj;
        public $files;
		private $urlTab;
		
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
		
		
		public function add($inData) {
            var_dump($this->data);
            $exist = false;
            foreach ($this->data as $data) {
                if ($inData == $data) {
                    //echo $inData."\n";
                    $exist=true;
                    break;
                }
            }
            if (!$exist) {
                $this->data[]=$inData;
            }
		}

		public function getData(){
			return $this->data;
		}
	}

/**
 * Début du moteur
 */

	$cacheManifest = new cacheManifest();
    $array = Spyc::YAMLLoad('./init.yml');

	foreach ($array['pages'] as $page) {
		$html = file_get_html($array['domain'].$page);
		$cacheManifest->addUrl($array['domain'].$page);
		
		foreach($html->find('link') as $element) {
			$cacheManifest->cssObj->add($element->href);
		}
		
		// Find all link
		foreach($html->find('script') as $element) {
			$cacheManifest->scriptObj->add($element->src);
		}
	
	}

    foreach ($array["resources"]["paths"] as $path) {
        $finder = new Finder();
        $finder->in($array["resources"]["realPath"].$path);

        foreach ($finder as $finderFile) {
            $cacheManifest->files->add(str_replace($array["resources"]["realPath"],'',$finderFile->getRealPath()));
        }
    }

	foreach ($array['sql'] as $sql) {
		$cacheManifest->sqlObj->add($sql);
	}
	$cacheManifest->generate();
	file_put_contents($array["manifest_path"].$array["manifest_name"],$cacheManifest->getFile());
?>
