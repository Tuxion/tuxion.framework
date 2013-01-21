{
  "message": "<?=$t->exception->getMessage()?>",
  "file": "<?=$t->exception->getFile()?>",
  "line" <?=$t->exception->getLine()?>,
  "trace": <?=wrap($t->exception->getTrace())->toJSON();?>
}
