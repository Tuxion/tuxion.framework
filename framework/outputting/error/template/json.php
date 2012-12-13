{
  "message": "<?=$t->exception->getMessage()?>",
  "trace": <?=wrap($t->exception->getTrace())->toJSON();?>
}
