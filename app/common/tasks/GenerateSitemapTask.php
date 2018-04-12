<?php
use Phalcon\Cli\Task;
use Phalcon\Logger\Adapter\File as FileAdapter;

class GenerateSitemapTask extends Task {
    public function mainAction() {
        //echo __METHOD__ . PHP_EOL . PHP_EOL;
		echo __METHOD__ . ". GenerateSitemapTask" . PHP_EOL;
		
		$XML = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
		
		$baseURL = 'http://' . $this->config['application']['publicHost'] . '/';
		$targetFile = $this->config['application']['publicHostDir'] . 'public/sitemap.xml';
		
		// index
		$XML .= '<url><loc>' . $baseURL . 'index/</loc>' . 
			//'<lastmod>2014-09-18T18:54:13+04:00</lastmod>' . 
			'<changefreq>always</changefreq><priority>1.0</priority></url>';
		// newslist
		$XML .= '<url><loc>' . $baseURL . 'newslist/</loc><changefreq>hourly</changefreq><priority>0.6</priority></url>';
		// about
		$XML .= '<url><loc>' . $baseURL . 'newslist/</loc><changefreq>daily</changefreq><priority>0.5</priority></url>';
		
		
		// organization, expenselist
		$rows = Organization::find();
		echo __METHOD__ . ". Total organizations count = " . count($rows) . ' ' . PHP_EOL;
		
		foreach($rows as $row) {
			//echo __METHOD__ . '. Organization id: ' . $row->id . PHP_EOL;
			$XML .= '<url><loc>' . $baseURL . 'organization?id=' . $row->id . '</loc><changefreq>daily</changefreq><priority>0.8</priority></url>';
			$XML .= '<url><loc>' . $baseURL . 'expenselist?filter_organization=' . $row->id . '</loc><changefreq>always</changefreq><priority>0.7</priority></url>';
		}
		
		$XML .= '</urlset>';
		
		echo __METHOD__ . '. New sitemap.xml: ' . $targetFile . PHP_EOL;
		
		file_put_contents($targetFile, $XML, /*FILE_APPEND |*/ LOCK_EX);
    }
}

