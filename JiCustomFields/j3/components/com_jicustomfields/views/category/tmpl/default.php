<?php
// Code marked with #Jinfinity author/copyright
/**
 * @version     $Id: default.php 015 2014-10-27 10:16:00Z Anton Wintergerst $
 * @package     JiCustomFields 2.1 Framework for Joomla 3.3.6
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
// Other code original author/copyright
/**
* @package     Joomla.Site
* @subpackage  com_content
*
* @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');
// #Jinfinity
JHtml::addIncludePath(JPATH_SITE.'/components/com_content/helpers');

JHtml::_('behavior.caption');
?>
<div class="category-list<?php echo $this->pageclass_sfx;?>">

    <?php
    $this->subtemplatename = 'articles';
    echo JLayoutHelper::render('joomla.content.category_default', $this);
    ?>

</div>