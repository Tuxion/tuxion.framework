<h1><?=$t->type?></h1>
<h2><?=$t->exception->getMessage()?></h2>

<p>
  <?=tx('Debug')->printTrace($t->exception->getTrace())?>
</p>
