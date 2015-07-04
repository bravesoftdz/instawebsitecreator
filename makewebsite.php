<?php
$domain = '';
if (isset($argv[1])){
   $domain = $argv[1];
}
echo "Welcome to Jims Insta-Site-Cretor\n";
$newsite = new NewSite($domain);






















class NewSite{
   public $domain;
   public $username;
   public $linode_dns_link = 'https://manager.linode.com/dns/domain/';
   public function __construct($domain = 'example.com'){

      if (empty($domain)){
         $domain = getLine('Enter domain without www (ie. example.com):');
      }
      $this->domain = $domain;
      $ex = explode('.',$domain, 2);
      $this->username = $ex[0];
      echo "Domain: ".$this->domain."\n";
      echo "Username: ".$this->username."\n";

      echo "Set up your DNS here: \n".$this->getLinodeDnsLink()."\n";
      echo "Set your nameservers to ns1.linode.com (1-5) (godaddy, namecheap, etc)\n";
      echo "Perform the commands below:\n";
      echo $this->getAddUserScript()."\n";
      echo "mkdir -p /home/$this->username/public_html"."\n";
      echo "mkdir -p /home/$this->username/logs"."\n";
      $sites_avail_file = "/etc/apache2/sites-available/{$this->domain}";
      if (file_exists($sites_avail_file)){
         echo ('Site already exists'."\n");
         $create_or_replace = 'Replace';
      }else{
         $create_or_replace = 'Create';
      }
         echo "You need an apache virtual host file such as the one below"."\n".
              "$sites_avail_file"."\n"."$create_or_replace it? [Y/n]";

         if (getLine() == 'Y'){
            file_put_contents($sites_avail_file, $this->genApacheSiteTemplate());
         }
      echo "Now please enable the site with the command below\n";
      echo "a2ensite $this->domain"."\n";

      echo "\n";echo "done.\n";
   }

   public function getAddUserScript(){
       return 'adduser '.$this->username;
   }

   public function getLinodeDnsLink(){
      return $this->linode_dns_link . $this->domain;
   }

   public function genApacheSiteTemplate(){
        $template = <<<APACHE
<VirtualHost *:80>
        ServerAdmin webmaster@{$this->domain}
        ServerName  {$this->domain}
        ServerAlias www.{$this->domain}

        # Indexes + Directory Root.
        DocumentRoot /home/{$this->username}/public_html
        DirectoryIndex index.php

        <IfModule mod_php5.c>
                AddType application/x-httpd-php .php

                php_flag magic_quotes_gpc Off
                php_flag track_vars On
                php_flag register_globals Off
                php_value include_path .
        </IfModule>

        # Logfiles
        ErrorLog  /home/{$this->username}/logs/error.log
        CustomLog /home/{$this->username}/logs/access.log combined
</VirtualHost>
APACHE;
        return $template;
   }
}

function getLine($q = ''){
   static $handle = null;
    if (empty($handle)){ $handle = fopen ("php://stdin","r"); }
   if (!empty($q)){ echo $q; }

   $line = fgets($handle);
   return trim($line);
}



