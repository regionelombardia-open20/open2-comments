<?php
/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 * @package   open20\amos-comments
 * @author    Aria S.p.A. <protocollo@pec.ariaspa.it>
 * @license   GNU General Public License v3.0 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @link      https://www.ariaspa.it/
 *
 * This file is part of OPEN 2.0.
 *
 * OPEN 2.0 is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OPEN 2.0 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OPEN 2.0. If not, see <https://www.gnu.org/licenses/>.
 */

use open20\amos\comments\assets\CommentsAsset;
use open20\amos\comments\AmosComments;
use open20\amos\admin\AmosAdmin;
use yii\helpers\Html;

$asset = CommentsAsset::register($this);

$labelSigninOrSignup = AmosComments::t('amoscomments', '#beforeActionCtaLoginRegister');
$titleSigninOrSignup = AmosComments::t(
    'amoscomments',
    '#beforeActionCtaLoginRegisterTitle',
    ['platformName' => \Yii::$app->name]
);
$labelSignin = AmosComments::t('amoscomments', '#beforeActionCtaLogin');
$titleSignin = AmosComments::t(
    'amoscomments',
    '#beforeActionCtaLoginTitle',
    ['platformName' => \Yii::$app->name]
);
$subtitleSigninOrSignup = AmosComments::t(
    'amoscomments',
    '#subtitleBoxPartecipaLoginRegister',
    ['platformName' => \Yii::$app->name]
);
$subtitleSignin = AmosComments::t(
    'amoscomments',
    '#subtitleBoxPartecipaLogin',
    ['platformName' => \Yii::$app->name]
);

$labelLink = $labelSigninOrSignup;
$titleLink = $titleSigninOrSignup;
$subtitleBannerCta = $subtitleSigninOrSignup;
$socialAuthModule = Yii::$app->getModule('socialauth');
if ($socialAuthModule && ($socialAuthModule->enableRegister == false)) {
    $labelLink = $labelSignin;
    $titleLink = $titleSignin;
    $subtitleBannerCta = $subtitleSignin;
}

$ctaLoginRegister = Html::a(
    $labelLink,
    isset(\Yii::$app->params['linkConfigurations']['loginLinkCommon']) ? \Yii::$app->params['linkConfigurations']['loginLinkCommon']
        : \Yii::$app->params['platform']['backendUrl'] . '/' . AmosAdmin::getModuleName() . '/security/login',
    [
        'title' => $titleLink,
        'class' => 'btn btn-lg btn-tertiary'
    ]
);

?>
<div class="container">
    <div id="box-partecipa" class="footer_box">
        <div class="uk-container">
            <span class="triangle"></span>
            <div class="row">
                <div class="col-md-9">
                    <p class="h3 m-t-15"><strong><?= AmosComments::t('amoscomments', '#titleBoxPartecipa'); ?></strong></p>
                    <p class="lead"><?= $subtitleBannerCta ?></p>
                </div>
                <div class="col-md-3 text-right m-t-20">
                    <?= $ctaLoginRegister ?>
                </div>
            </div>
        </div>
    </div>
</div>