<?php echo $this->extend('layout'); ?>
<?php echo $this->section('content'); ?>

<a href="<?php echo base_url('/'); ?>" class="btn btn-warning">Retour aux cartes</a>

<?php if (empty($deletedItems)) { ?>
<div class="alert alert-info" role="alert">
    Aucune carte supprimée pour le moment.
</div>
<?php } else { ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th scope="col">Titre</th>
                <th scope="col">Auteur</th>
                <th scope="col">Date de suppression</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($deletedItems as $item) { ?>
            <tr>
                <td><?php echo htmlspecialchars($item->titre); ?></td>
                <td><?php echo htmlspecialchars($item->author_name ?? ''); ?></td>
                <td><?php echo htmlspecialchars((string) $item->deleted_at); ?></td>
                <td>
                    <a href="<?php echo base_url('item/restore/' . $item->id); ?>"
                        class="btn btn-success btn-sm">Restaurer</a>
                    <a href="<?php echo base_url('item/permanent-delete/' . $item->id); ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cette carte ?');">Supprimer
                        définitivement</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<?php } ?>
<?php echo $this->endSection(); ?>