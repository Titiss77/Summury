<?php

use CodeIgniter\Pager\PagerRenderer;

// @var PagerRenderer $pager
$pager->setSurroundCount(0);
?>
<nav>
	<ul class="pager">
		<li <?php echo $pager->hasPrevious() ? '' : 'class="disabled"'; ?>>
			<a href="<?php echo $pager->getPrevious() ?? '#'; ?>" aria-label="<?php echo lang('Pager.previous'); ?>">
				<span aria-hidden="true"><?php echo lang('Pager.newer'); ?></span>
			</a>
		</li>
		<li <?php echo $pager->hasNext() ? '' : 'class="disabled"'; ?>>
			<a href="<?php echo $pager->getNext() ?? '#'; ?>" aria-label="<?php echo lang('Pager.next'); ?>">
				<span aria-hidden="true"><?php echo lang('Pager.older'); ?></span>
			</a>
		</li>
	</ul>
</nav>
