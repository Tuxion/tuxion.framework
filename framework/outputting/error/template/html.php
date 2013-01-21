<h1><?=$t->type?></h1>
<h2><?=$t->exception->getMessage()?></h2>
<h4><?=$t->exception->getFile()?>:<?=$t->exception->getLine()?></h4>

<p>
  <?=tx('Debug')->printTrace($t->exception->getTrace())?>
</p>
