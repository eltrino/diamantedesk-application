<?php

if (!isset($_SERVER['HTTP_HOST'])) {
    exit('This script cannot be run from the CLI. Run it from a browser.');
}

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../app/DiamanteDeskRequirements.php';
require_once __DIR__ . '/../app/autoload.php';

// check for installed system
$paramFile = __DIR__ . '/../app/config/parameters.yml';

if (file_exists($paramFile)) {
    $data = Yaml::parse($paramFile);

    if (is_array($data)
        && isset($data['parameters'])
        && isset($data['parameters']['installed'])
        && false != $data['parameters']['installed']
    ) {
        exit('DiamanteDesk is already installed.');
    }
}

/**
 * @todo Identify correct locale (headers?)
 */
$locale           = 'en';
$collection       = new DiamanteDeskRequirements();
$translator       = new Translator($locale);
$majorProblems    = $collection->getFailedRequirements();
$minorProblems    = $collection->getFailedRecommendations();

$translator->addLoader('yml', new YamlFileLoader());
$translator->addResource('yml', __DIR__ . '/../app/Resources/translations/install.' . $locale . '.yml', $locale);

function iterateRequirements(array $collection)
{
    foreach ($collection as $requirement) :
        ?>
        <tr>
            <td class="dark">
                <?php if ($requirement->isFulfilled()) : ?>
                <span class="icon-yes">
            <?php elseif (!$requirement->isOptional()) : ?>
                    <span class="icon-no">
            <?php else : ?>
                        <span class="icon-warning">
            <?php endif; ?>
            <?php echo $requirement->getTestMessage(); ?>
            </span>
            <?php if ($requirement instanceof CliRequirement && !$requirement->isFulfilled()) : ?>
                <pre class="output"><?php echo $requirement->getOutput(); ?></pre>
            <?php endif; ?>
            </td>
            <td><?php echo $requirement->isFulfilled() ? 'OK' : $requirement->getHelpHtml(); ?></td>
        </tr>
    <?php
    endforeach;
}
?>
<!doctype html>
<!--[if IE 7 ]><html class="no-js ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="no-js ie ie8" lang="en"> <![endif]-->
<!--[if IE 9 ]><html class="no-js ie ie9" lang="en"> <![endif]-->
<!--[if (gte IE 10)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title><?php echo $translator->trans('title'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="manifest" href="bundles/diamantedistribution/img/ico/manifest.json">
    <link rel="apple-touch-icon-precomposed" sizes="57x57" href="bundles/diamantedistribution/img/ico/apple-touch-icon-57x57.png" />
    <link rel="apple-touch-icon-precomposed" sizes="60x60" href="bundles/diamantedistribution/img/ico/apple-touch-icon-60x60.png" />
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="bundles/diamantedistribution/img/ico/apple-touch-icon-72x72.png" />
    <link rel="apple-touch-icon-precomposed" sizes="76x76" href="bundles/diamantedistribution/img/ico/apple-touch-icon-76x76.png" />
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="bundles/diamantedistribution/img/ico/apple-touch-icon-114x114.png" />
    <link rel="apple-touch-icon-precomposed" sizes="120x120" href="bundles/diamantedistribution/img/ico/apple-touch-icon-120x120.png" />
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="bundles/diamantedistribution/img/ico/apple-touch-icon-144x144.png" />
    <link rel="apple-touch-icon-precomposed" sizes="152x152" href="bundles/diamantedistribution/img/ico/apple-touch-icon-152x152.png" />

    <link rel="icon" type="image/png" href="bundles/diamantedistribution/img/ico/favicon-16x16.png" sizes="16x16" />
    <link rel="icon" type="image/png" href="bundles/diamantedistribution/img/ico/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="bundles/diamantedistribution/img/ico/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/png" href="bundles/diamantedistribution/img/ico/favicon-194x194.png" sizes="194x194" />

    <meta name="application-name" content="<?php echo $translator->trans('title'); ?>"/>
    <meta name="msapplication-TileColor" content="#f6fbff" />
    <meta name="msapplication-TileImage" content="bundles/diamantedistribution/img/ico/mstile-144x144.png" />
    <meta name="theme-color" content="#ffffff">

    <link rel="stylesheet" type="text/css" href="bundles/oroinstaller/css/style.css" />
    <link rel="stylesheet" type="text/css" href="bundles/diamantedistribution/css/style.css" />

    <script type="text/javascript" src="bundles/oroinstaller/lib/jquery-2.0.3.min.js"></script>
    <script type="text/javascript">
        $(function() {
            var splash = $('div.start-box'),
                body = $('body'),
                winHeight = $(window).height();

            $('#begin-install').click(function() {
                splash.hide();
                body.css({ 'overflow': 'visible', 'height': 'auto' });
            });

            if ('localStorage' in window && window['localStorage'] !== null) {
                if (!localStorage.getItem('oroInstallSplash')) {
                    splash.show().height(winHeight);
                    body.css({ 'overflow': 'hidden', 'height': winHeight });

                    localStorage.setItem('oroInstallSplash', true);
                }
            }

            <?php if (!count($majorProblems)) : ?>
            // initiate application in background
            $.get('installer/flow/oro_installer/configure');
            <?php endif; ?>
        });
    </script>
</head>
<body>
<header class="header">
        <h1 class="logo">
            <img src="bundles/diamantedistribution/img/logo.svg" alt="<?php echo $translator->trans('title'); ?>"/>
        </h1>
</header>
<div class="wrapper">
    <div class="content">
        <div class="progress-bar">
            <ul>
                <li class="active">
                    <strong class="step">1</strong>
                    <span><?php echo $translator->trans('process.step.check.header'); ?></span>
                </li>
                <li>
                    <strong class="step">2</strong>
                    <span><?php echo $translator->trans('process.step.configure'); ?></span>
                </li>
                <li>
                    <strong class="step">3</strong>
                    <span><?php echo $translator->trans('process.step.diamanteinstall'); ?></span>
                </li>
                <li>
                    <strong class="step">4</strong>
                    <span><?php echo $translator->trans('process.step.setup'); ?></span>
                </li>
                <li>
                    <strong class="step">5</strong>
                    <span><?php echo $translator->trans('process.step.final'); ?></span>
                </li>
            </ul>
        </div>

        <div class="page-title">
            <h2><?php echo $translator->trans('process.step.check.header'); ?></h2>
        </div>

        <div class="well">
            <?php if (count($majorProblems)) : ?>
                <div class="validation-error">
                    <ul>
                        <li><?php echo $translator->trans('process.step.check.invalid'); ?></li>
                        <?php if ($collection->hasPhpIniConfigIssue()): ?>
                            <li id="phpini">*
                                <?php
                                if ($collection->getPhpIniConfigPath()) :
                                    echo $translator->trans(
                                        'process.step.check.phpchanges',
                                        array(
                                            '%path%' => $collection->getPhpIniConfigPath()
                                        )
                                    );
                                else :
                                    echo $translator->trans('process.step.check.phpchanges');
                                endif;
                                ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php
            $requirements = array(
                'mandatory' => $collection->getMandatoryRequirements(),
                'php'       => $collection->getPhpIniRequirements(),
                'oro'       => $collection->getOroRequirements(),
                'diamante'  => $collection->getDiamanteDeskRequirements(),
                'cli'       => $collection->getCliRequirements(),
                'optional'  => $collection->getRecommendations(),
            );

            foreach($requirements as $type => $requirement) : ?>
                <table class="table">
                    <col width="75%" valign="top">
                    <col width="25%" valign="top">
                    <thead>
                    <tr>
                        <th><?php echo $translator->trans('process.step.check.table.' . $type); ?></th>
                        <th><?php echo $translator->trans('process.step.check.table.check'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php iterateRequirements($requirement); ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        </div>
        <div class="button-set">
            <div class="pull-right">
                <?php if (count($majorProblems) || count($minorProblems)): ?>
                    <a href="install.php" class="button icon-reset">
                        <span><?php echo $translator->trans('process.button.refresh'); ?></span>
                    </a>
                <?php endif; ?>
                <a href="<?php echo count($majorProblems) ? 'javascript: void(0);' : 'installer'; ?>" class="button next <?php echo count($majorProblems) ? 'disabled' : 'primary'; ?>">
                    <span><?php echo $translator->trans('process.button.next'); ?></span>
                </a>
            </div>
        </div>
    </div>
</div>
<div class="start-box" style="display: none;">
    <div class="fade-box"></div>
    <div class="start-content">
        <div class="start-content-holder">
            <div class="center"><img alt="Install" src="bundles/oroinstaller/img/cloud.png" /></div>
            <h2><?php echo $translator->trans('welcome.header'); ?></h2>
            <h3><?php echo $translator->trans('welcome.content'); ?></h3>
            <div class="start-footer">
                <button type="button" id="begin-install" class="primary button next" href="javascript: void(0);"><span><?php echo $translator->trans('welcome.button'); ?></span></button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
