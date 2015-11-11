<?php
/**
* @OroScript("Install script")
**/
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

$kernel = $container->get('kernel');
$fs = new Filesystem();
$rootDir = $kernel->getRootDir();
$webDir = realpath($rootDir . '/../web');

try {
    $fs->mkdir($rootDir . '/attachments');
} catch (IOExceptionInterface $e) {
    echo "An error occurred while creating your directory at ".$e->getPath();
}

try {
    $fs->mkdir($webDir . '/uploads/branch/logo');
} catch (IOExceptionInterface $e) {
    echo "An error occurred while creating your directory at ".$e->getPath();
}


$application = new Application($kernel);
$application->setAutoExit(false);

$commands = array(
    array('command' =>  'oro:platform:update', '--force' => true),
    array('command' =>  'diamante:desk:data'),
);

foreach($commands as $command) {
    $output = new BufferedOutput();
    $input = new ArrayInput($command);
    $application->run($input, $output);
}

$yamlParser = new Yaml();
$yamlArray = $yamlParser->parse(file_get_contents($rootDir . '/config/security.yml'));

if (isset($yamlArray['security'])) {
    $yamlArray['security']['firewalls'] = array_merge($yamlArray['security']['firewalls'],
        array(
            //DiamanteDeskBundle
            'diamante_attachments_download' => array(
                'pattern' => '^/desk/attachments/download/*',
                'provider' => 'chain_provider',
                'anonymous' => true,
            ),
            //DiamanteFrontBundle
            'front_diamante' => array(
                'pattern' => '^/portal',
                'provider' => 'chain_provider',
                'anonymous' => true,
            ),
            'front_diamante_reset_password' => array(
                'pattern' => '^/portal/password/*',
                'provider' => 'chain_provider',
                'anonymous' => true,
            ),
            //DiamanteEmbeddedFormBundle
            'diamante_embedded_form' => array(
                'pattern' => '^/embedded-form/submit-ticket',
                'provider' => 'chain_provider',
                'anonymous' => true,
            ),
            //DiamanteApiBundle
            'wsse_secured_diamante' => array(
                'pattern' => '^/api/diamante/(rest|soap).*',
                'provider' => 'diamante_api_user',
                'stateless' => true,
                'wsse_diamante_api' => true,
            )
        )
    );
    $yamlArray['security']['providers'] = array_merge($yamlArray['security']['providers'], array(
        //DiamanteApiBundle
        'diamante_api_user' => array(
            'id' => 'diamante.api.user.security.provider',
        ))
    );
}

$yaml = $yamlParser->dump($yamlArray, 10);

file_put_contents($rootDir . '/config/security.yml', $yaml);

