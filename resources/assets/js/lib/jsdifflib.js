/*
This is part of jsdifflib v1.0. <http://github.com/cemerick/jsdifflib>

Copyright 2007 - 2011 Chas Emerick <cemerick@snowtide.com>. All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

1. Redistributions of source code must retain the above copyright notice, this list of
conditions and the following disclaimer.

2. Redistributions in binary form must reproduce the above copyright notice, this list
of conditions and the following disclaimer in the documentation and/or other materials
provided with the distribution.

THIS SOFTWARE IS PROVIDED BY Chas Emerick ``AS IS'' AND ANY EXPRESS OR IMPLIED
WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND
FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL Chas Emerick OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF
ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

The views and conclusions contained in the software and documentation are those of the
authors and should not be interpreted as representing official policies, either expressed
or implied, of Chas Emerick.
*/
__whitespace={" ":!0,"\t":!0,"\n":!0,"\u000c":!0,"\r":!0};
difflib={defaultJunkFunction:function(a){return a in __whitespace},stripLinebreaks:function(a){return a.replace(/^[\n\r]*|[\n\r]*$/g,"")},stringAsLines:function(a){for(var c=a.indexOf("\n"),i=a.indexOf("\r"),a=a.split(c>-1&&i>-1||i<0?"\n":"\r"),c=0;c<a.length;c++)a[c]=difflib.stripLinebreaks(a[c]);return a},__reduce:function(a,c,i){if(i!=null)var d=0;else if(c)i=c[0],d=1;else return null;for(;d<c.length;d++)i=a(i,c[d]);return i},__ntuplecomp:function(a,c){for(var i=Math.max(a.length,c.length),d=0;d<
i;d++){if(a[d]<c[d])return-1;if(a[d]>c[d])return 1}return a.length==c.length?0:a.length<c.length?-1:1},__calculate_ratio:function(a,c){return c?2*a/c:1},__isindict:function(a){return function(c){return c in a}},__dictget:function(a,c,i){return c in a?a[c]:i},SequenceMatcher:function(a,c,i){this.set_seqs=function(d,p){this.set_seq1(d);this.set_seq2(p)};this.set_seq1=function(d){if(d!=this.a)this.a=d,this.matching_blocks=this.opcodes=null};this.set_seq2=function(d){if(d!=this.b)this.b=d,this.matching_blocks=
this.opcodes=this.fullbcount=null,this.__chain_b()};this.__chain_b=function(){for(var d=this.b,p=d.length,b=this.b2j={},a={},g=0;g<d.length;g++){var e=d[g];if(e in b){var h=b[e];p>=200&&h.length*100>p?(a[e]=1,delete b[e]):h.push(g)}else b[e]=[g]}for(e in a)delete b[e];d=this.isjunk;p={};if(d){for(e in a)d(e)&&(p[e]=1,delete a[e]);for(e in b)d(e)&&(p[e]=1,delete b[e])}this.isbjunk=difflib.__isindict(p);this.isbpopular=difflib.__isindict(a)};this.find_longest_match=function(d,a,b,r){for(var g=this.a,
e=this.b,h=this.b2j,j=this.isbjunk,l=d,m=b,f=0,c=null,i={},v=[],n=d;n<a;n++){var u={},w=difflib.__dictget(h,g[n],v),t;for(t in w)if(c=w[t],!(c<b)){if(c>=r)break;u[c]=k=difflib.__dictget(i,c-1,0)+1;k>f&&(l=n-k+1,m=c-k+1,f=k)}i=u}for(;l>d&&m>b&&!j(e[m-1])&&g[l-1]==e[m-1];)l--,m--,f++;for(;l+f<a&&m+f<r&&!j(e[m+f])&&g[l+f]==e[m+f];)f++;for(;l>d&&m>b&&j(e[m-1])&&g[l-1]==e[m-1];)l--,m--,f++;for(;l+f<a&&m+f<r&&j(e[m+f])&&g[l+f]==e[m+f];)f++;return[l,m,f]};this.get_matching_blocks=function(){if(this.matching_blocks!=
null)return this.matching_blocks;for(var d=this.a.length,a=this.b.length,b=[[0,d,0,a]],c=[],g,e,h,j,l,m,f,i;b.length;)if(j=b.pop(),g=j[0],e=j[1],h=j[2],j=j[3],i=this.find_longest_match(g,e,h,j),l=i[0],m=i[1],f=i[2])c.push(i),g<l&&h<m&&b.push([g,l,h,m]),l+f<e&&m+f<j&&b.push([l+f,e,m+f,j]);c.sort(difflib.__ntuplecomp);b=j1=k1=block=0;g=[];for(var q in c)block=c[q],i2=block[0],j2=block[1],k2=block[2],b+k1==i2&&j1+k1==j2?k1+=k2:(k1&&g.push([b,j1,k1]),b=i2,j1=j2,k1=k2);k1&&g.push([b,j1,k1]);g.push([d,
a,0]);return this.matching_blocks=g};this.get_opcodes=function(){if(this.opcodes!=null)return this.opcodes;var d=0,a=0,b=[];this.opcodes=b;var c,g,e,h,j=this.get_matching_blocks(),l;for(l in j)c=j[l],g=c[0],e=c[1],c=c[2],h="",d<g&&a<e?h="replace":d<g?h="delete":a<e&&(h="insert"),h&&b.push([h,d,g,a,e]),d=g+c,a=e+c,c&&b.push(["equal",g,d,e,a]);return b};this.get_grouped_opcodes=function(d){d||(d=3);var a=this.get_opcodes();a||(a=[["equal",0,1,0,1]]);var b,c,g,e,h;a[0][0]=="equal"&&(b=a[0],c=b[0],g=
b[1],e=b[2],h=b[3],b=b[4],a[0]=[c,Math.max(g,e-d),e,Math.max(h,b-d),b]);a[a.length-1][0]=="equal"&&(b=a[a.length-1],c=b[0],g=b[1],e=b[2],h=b[3],b=b[4],a[a.length-1]=[c,g,Math.min(e,g+d),h,Math.min(b,h+d)]);var j=d+d,l=[],m;for(m in a)b=a[m],c=b[0],g=b[1],e=b[2],h=b[3],b=b[4],c=="equal"&&e-g>j&&(l.push([c,g,Math.min(e,g+d),h,Math.min(b,h+d)]),g=Math.max(g,e-d),h=Math.max(h,b-d)),l.push([c,g,e,h,b]);l&&l[l.length-1][0]=="equal"&&l.pop();return l};this.ratio=function(){matches=difflib.__reduce(function(a,
c){return a+c[c.length-1]},this.get_matching_blocks(),0);return difflib.__calculate_ratio(matches,this.a.length+this.b.length)};this.quick_ratio=function(){var a,c;if(this.fullbcount==null){this.fullbcount=a={};for(var b=0;b<this.b.length;b++)c=this.b[b],a[c]=difflib.__dictget(a,c,0)+1}a=this.fullbcount;for(var i={},g=difflib.__isindict(i),e=numb=0,b=0;b<this.a.length;b++)c=this.a[b],numb=g(c)?i[c]:difflib.__dictget(a,c,0),i[c]=numb-1,numb>0&&e++;return difflib.__calculate_ratio(e,this.a.length+this.b.length)};
this.real_quick_ratio=function(){var a=this.a.length,c=this.b.length;return _calculate_ratio(Math.min(a,c),a+c)};this.isjunk=i?i:difflib.defaultJunkFunction;this.a=this.b=null;this.set_seqs(a,c)}};
diffview={buildView:function(a){function c(a,c){var b=document.createElement(a);b.className=c;return b}function i(a,c){var b=document.createElement(a);b.appendChild(document.createTextNode(c));return b}function d(a,c,b){a=document.createElement(a);a.className=c;a.appendChild(document.createTextNode(b));return a}function p(a,b,e,f,g){return b<e?(a.appendChild(i("th",(b+1).toString())),a.appendChild(d("td",g,f[b].replace(/\t/g,"\u00a0\u00a0\u00a0\u00a0"))),b+1):(a.appendChild(document.createElement("th")),
a.appendChild(c("td","empty")),b)}function b(a,b,c,e,f){a.appendChild(i("th",b==null?"":(b+1).toString()));a.appendChild(i("th",c==null?"":(c+1).toString()));a.appendChild(d("td",f,e[b!=null?b:c].replace(/\t/g,"\u00a0\u00a0\u00a0\u00a0")))}var r=a.baseTextLines,g=a.newTextLines,e=a.opcodes,h=a.baseTextName?a.baseTextName:"Base Text",j=a.newTextName?a.newTextName:"New Text",l=a.contextSize,a=a.viewType==0||a.viewType==1?a.viewType:0;if(r==null)throw"Cannot build diff view; baseTextLines is not defined.";
if(g==null)throw"Cannot build diff view; newTextLines is not defined.";if(!e)throw"Canno build diff view; opcodes is not defined.";var m=document.createElement("thead"),f=document.createElement("tr");m.appendChild(f);a?(f.appendChild(document.createElement("th")),f.appendChild(document.createElement("th")),f.appendChild(d("th","texttitle",h+" vs. "+j))):(f.appendChild(document.createElement("th")),f.appendChild(d("th","texttitle",h)),f.appendChild(document.createElement("th")),f.appendChild(d("th",
"texttitle",j)));for(var m=[m],h=[],s,j=0;j<e.length;j++){code=e[j];change=code[0];for(var q=code[1],v=code[2],n=code[3],u=code[4],w=Math.max(v-q,u-n),t=[],x=[],o=0;o<w;o++){if(l&&e.length>1&&(j>0&&o==l||j==0&&o==0)&&change=="equal")if(s=w-(j==0?1:2)*l,s>1)if(t.push(f=document.createElement("tr")),q+=s,n+=s,o+=s-1,f.appendChild(i("th","...")),a||f.appendChild(d("td","skip","")),f.appendChild(i("th","...")),f.appendChild(d("td","skip","")),j+1==e.length)break;else continue;t.push(f=document.createElement("tr"));
a?change=="insert"?b(f,null,n++,g,change):change=="replace"?(x.push(s=document.createElement("tr")),q<v&&b(f,q++,null,r,"delete"),n<u&&b(s,null,n++,g,"insert")):change=="delete"?b(f,q++,null,r,change):b(f,q++,n++,r,change):(q=p(f,q,v,r,change),n=p(f,n,u,g,change))}for(o=0;o<t.length;o++)h.push(t[o]);for(o=0;o<x.length;o++)h.push(x[o])}h.push(f=d("th","author","diff view generated by "));f.setAttribute("colspan",a?3:4);f.appendChild(s=i("a","jsdifflib"));s.setAttribute("href","http://github.com/cemerick/jsdifflib");
m.push(f=document.createElement("tbody"));for(j in h)f.appendChild(h[j]);f=c("table","diff"+(a?" inlinediff":""));for(j in m)f.appendChild(m[j]);return f}};
