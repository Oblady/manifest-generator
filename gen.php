<?php
    require_once('vendor/autoload.php');
    //namespace Oblady;
	include("./simple_html_dom.php");
	include('./spyc-0.5/spyc.php');

    use Symfony\Component\Finder\Finder;
	$array = Spyc::YAMLLoad('./init.yml');



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
			
			foreach ($this->cssObj as $obj) {
				var_dump($obj);
				$this->addLine($obj->getData());
			}

            $this->addCommentLine('Uploaded files');

            //var_dump($this->files);die;
            $temp = $this->files;
            foreach ($this->files->getData() as $uploadedFile) {
                $this->addLine($uploadedFile);
            }

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
			if (!array_search($inData,$this->data)) {
				$this->data[]=$inData;
			}
		}
		public function getData(){
			return $this->data;
		}
	}

	$cacheManifest = new cacheManifest();



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
	var_dump($cacheManifest->getFile());
?>
