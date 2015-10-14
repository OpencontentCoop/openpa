{def $n='' $attribute='' $url=false() $protocols=array('http', 'file', 'ftp', 'mailto', 'https')
}{if $protocols|contains( $href|explode(':')|extract_left(1))not()
}{set $n=fetch(content, node, hash(node_path, $href))
}{if and($n, $n.object|has_attribute('file'))
}{set $attribute=$n.object|attribute('file')
}{set $url=concat( '/content/download/', $attribute.contentobject_id, '/', $attribute.id,'/version/', $attribute.version , '/file/', $attribute.content.original_filename|urlencode )
}{/if}{/if}{if $url|not()}{set $url=$href}{/if}<a href={$url|ezurl}{section show=$id} id="{$id}"{/section}{section show=$title} title="{$title}"{/section}{section show=$target} target="{$target}"{/section}{section show=ne($classification|trim,'')} class="{$classification|wash}"{/section}>{$content}</a>{undef $n $attribute $url $protocols}