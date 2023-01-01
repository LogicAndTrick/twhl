<OpenSearchDescription
  xmlns="http://a9.com/-/spec/opensearch/1.1/"
  xmlns:moz="http://www.mozilla.org/2006/browser/search/">

  <ShortName>TWHL</ShortName>
  <Description>The Whole Half-Life - Search forum threads, wiki articles, maps, and more</Description>
  <Tags>GoldSource GoldSrc Half-Life Hammer Mapping Maps Modding Mods Source Tutorials TWHL Valve</Tags>
  
  <Image type="image/svg+xml">{{ asset('images/twhl-logo.svg') }}</Image>
  <Image width="64" height="64" type="image/png">{{ asset('images/logo_icon_64.png') }}</Image>

  <Url type="text/html" template="{{ act('search', 'index') }}?search={searchTerms}" />
  <moz:SearchForm>{{ act('search', 'index') }}</moz:SearchForm>
</OpenSearchDescription>
