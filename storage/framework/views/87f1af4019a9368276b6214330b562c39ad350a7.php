<?php echo e(Form::model($creditNote, array('route' => array('invoice.edit.credit.note',$creditNote->invoice, $creditNote->id), 'method' => 'post'))); ?>

<div class="modal-body">
    <div class="row">
        <div class="form-group  col-md-6">
            <?php echo e(Form::label('date', __('Date'),['class'=>'form-label'])); ?>

           
                <?php echo e(Form::date('date',null,array('class'=>'form-control','required'=>'required'))); ?>


            
        </div>
        <div class="form-group  col-md-6">
            <?php echo e(Form::label('amount', __('Amount'),['class'=>'form-label'])); ?>

           
                <?php echo e(Form::number('amount', null, array('class' => 'form-control','required'=>'required','step'=>'0.01'))); ?>

           
        </div>
        <div class="form-group col-md-12">
            <?php echo e(Form::label('description', __('Description'),['class'=>'form-label'])); ?>

            <?php echo Form::textarea('description', null, ['class'=>'form-control','rows'=>'3']); ?>

        </div>


    </div>
</div>
<div class="modal-footer">
    <input type="button" value="<?php echo e(__('Cancel')); ?>" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="<?php echo e(__('Update')); ?>" class="btn  btn-primary">
</div>
<?php echo e(Form::close()); ?>

<?php /**PATH /Applications/MAMP/htdocs/erp/resources/views/creditNote/edit.blade.php ENDPATH**/ ?>