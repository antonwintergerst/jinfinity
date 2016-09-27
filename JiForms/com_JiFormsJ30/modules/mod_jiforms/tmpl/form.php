<?php
/**
 * @version     $Id: form.php 011 2014-02-18 22:53:00Z Anton Wintergerst $
 * @package     JiForms for Joomla 3.0
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
ob_start();
if(isset($form)) eval('?>'.$form->content.'<?php ');
$formcontent = ob_get_clean();
if(!empty($formcontent)): ?>
    <div class="jiforms modjiforms">
        <?php echo $formcontent; ?>
    </div>
<?php endif; ?>