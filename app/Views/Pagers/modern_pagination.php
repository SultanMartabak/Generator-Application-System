<?php
/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 * @var string $pagerGroup
 */
$pager->setSurroundCount(2);
?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>">
    <ul class="pagination pagination-rounded shadow-sm">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getFirst() ?>" class="page-link bg-white border-0 rounded-circle shadow-sm mx-1" aria-label="<?= lang('Pager.first') ?>">
                    <i class="bi bi-chevron-double-left"></i>
                </a>
            </li>
            <li class="page-item">
                <a href="<?= $pager->getPrevious() ?>" class="page-link bg-white border-0 rounded-circle shadow-sm mx-1" aria-label="<?= lang('Pager.previous') ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
         
            <?php if ($link['title'] === '...') : ?>
                <li class="page-item disabled">
                    <span class="page-link bg-transparent border-0 mx-1">...</span>
                </li>
            <?php else : ?>
                <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                    <a href="<?= $link['uri'] ?>" class="page-link bg-white border-0 rounded-circle shadow-sm mx-1<?= $link['active'] ? ' text-white bg-primary' : '' ?>">
                        <?= $link['title'] ?>
                    </a>
                </li>
            <?php endif ?>
        <?php endforeach ?>
        <?php if ($pager->getPageCount() <= 1 && $pager->getCurrentPageNumber() === 1 && $pager->getTotal() > 0) : ?>
            <!-- Display current page number even if there's only one page -->
            <li class="page-item active">
                <span class="page-link bg-white border-0 rounded-circle shadow-sm mx-1 text-white bg-primary active">
                    <?= $pager->getCurrentPageNumber() ?>
                </span>
            </li>
        <?php endif; ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getNext() ?>" class="page-link bg-white border-0 rounded-circle shadow-sm mx-1" aria-label="<?= lang('Pager.next') ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            <li class="page-item">
                <a href="<?= $pager->getLast() ?>" class="page-link bg-white border-0 rounded-circle shadow-sm mx-1" aria-label="<?= lang('Pager.last') ?>">
                    <i class="bi bi-chevron-double-right"></i>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>