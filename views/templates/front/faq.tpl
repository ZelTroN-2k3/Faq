{capture name=path}
{l s='FAQ - Domande frequenti' mod='faq'}
{/capture}
<div class="container">
<h4>FAQ</h4><hr>
{$i = 0|truncate:0:""}
{foreach from=$faqs item=faq}
{$i++|truncate:0:""}
  <a href="#{$faq.id_qa}" class="questionbutton" data-toggle="collapse">{$i} - {$faq.question}</a>
  <div id="{$faq.id_qa}" class="collapse">
        {$faq.answer}
  </div>
<br>
{/foreach}

</div>
