"use strict";(self.webpackChunkrealCookieBanner_=self.webpackChunkrealCookieBanner_||[]).push([[3],{76010:(e,t,a)=>{a.d(t,{JJ:()=>n});var n=function(e){return e.Free="free",e.Pro="pro",e}(n||{})},61415:(e,t,a)=>{a.d(t,{G:()=>g});var n=a(46270),r=a(87363),c=a(76010),o=a(24772),l=a(69017),s=a(40866),i=a(35392),u=a(60204),d=a(34139),m=a(71414);const{Meta:p}=o.Z;function f(e){let{template:t,renderActions:a,onSelect:n,grayOutAlreadyExisting:r}=e;const{__:f,_i:h}=(0,u.Q)(),{isPro:g,isDemoEnv:R}=(0,d.$)(),{headline:y,subHeadline:E,logoUrl:b,tier:_,consumerData:{tags:k,isDisabled:w,isCreated:v}}=t,{technicalHandlingIntegration:x}=t.consumerData,C=f("Disabled"),S=_===c.JJ.Pro&&(!g||R),{open:T,modal:$}=(0,m.T)({title:f("Want to use %s template?",y),feature:"preset",description:`${f("Only a limited number of templates for services and content blockers are available in the %s version of Real Cookie Banner. Get the PRO version now and create a service or content blocker from this template with just one click!",f(R?"Demo":"Free").toLowerCase())}${R?"":`\n\n${f("You can create this service yourself in the free version without any restrictions and research the necessary information.")}`}`},!R&&void 0),Z=e=>{e.target.closest(".rcb-antd-card")&&(S?T():w||null==n||n(t))},U=React.createElement("img",{style:{width:"90%"},src:b});return React.createElement(React.Fragment,null,$,React.createElement(l.Z,{title:w?React.createElement("span",{dangerouslySetInnerHTML:{__html:k[C]}}):void 0},React.createElement(o.Z,{className:"rcb-antd-template-card",hoverable:!w,style:{opacity:w||r&&v?.6:1},onClick:Z,cover:x?React.createElement(s.Z.Ribbon,{text:React.createElement(l.Z,{title:h(f("The {{strong}}%s{{/strong}} plugin is recommending this service.",x.name),{strong:React.createElement("strong",null)})},React.createElement("div",null,f("Integration")))},U):U,actions:a?a(t,Z):[]},React.createElement(p,{title:React.createElement("span",null,S&&React.createElement(i.Z,{color:m.t},"PRO"),!!k&&Object.keys(k).map((e=>React.createElement(l.Z,{title:e===C?void 0:React.createElement("span",{dangerouslySetInnerHTML:{__html:k[e]}}),key:e},React.createElement(i.Z,null,e)))),React.createElement("br",null),y),description:E||React.createElement("i",null,f("No description"))}))))}var h=a(29894);function g(e){let{templates:t,showHidden:a,showDisabled:c=!0,...o}=e;const[l,s]=(0,r.useState)(!1);return(0,r.useEffect)((()=>{!l&&t.length>0&&s(!0)}),[l,t.length]),t.length>0||l?React.createElement(React.Fragment,null,t.filter((e=>{let{isHidden:t}=e;return!!a||!t})).filter((e=>{let{consumerData:{isDisabled:t}}=e;return!!c||!t})).map((e=>React.createElement(f,(0,n.Z)({template:e,key:e.identifier},o))))):React.createElement(h.Z,{spinning:!0,style:{marginTop:10}})}},47620:(e,t,a)=>{a.d(t,{Y:()=>o});var n=a(87363),r=a(15998),c=a.n(r);const o=e=>{let{settings:t={},value:a="",onChange:r}=e;const o=(0,n.useRef)(),l=(0,n.useRef)(),{codeEditor:s}=c();(0,n.useEffect)((()=>{if(s){const{codemirror:e}=s.initialize(o.current,t);l.current=e,e.on("change",(e=>{null==r||r(e.getValue())}))}}),[]);const i=(0,n.useCallback)((()=>{}),[]);return(0,n.useEffect)((()=>{"string"==typeof a&&o.current&&l.current&&o.current.value!==l.current.getValue()&&l.current.setValue(o.current.value)}),[a]),React.createElement("textarea",{ref:o,value:a,onChange:s?i:e=>{let{target:{value:t}}=e;return r(t)},style:{width:"100%"}})}},45567:(e,t,a)=>{a.d(t,{f:()=>n});const n=e=>{let{children:t,maxWidth:a="auto",style:n={}}=e;return React.createElement("div",{className:"rcb-config-content",style:{maxWidth:"fixed"===a?1300:a,...n}},t)}},82434:(e,t,a)=>{a.r(t),a.d(t,{ScannerList:()=>le});var n=a(87363),r=a(68038),c=a(29894),o=a(88122),l=a(79635),s=a(40045),i=a(70006),u=a(49048),d=a(63593),m=a(50675);const p=/.+?:\/\/.+?(\/.+?)(?:#|\?(.*)|$)/;function f(e,t){var a;let n=arguments.length>2&&void 0!==arguments[2]?arguments[2]:"";const r=(null===(a=e.match(p))||void 0===a?void 0:a[1])||"/",c=t.match(p);if(c){const[,t,a]=c,o=a?`?${a}${n?`?${n}`:""}`:n?`?${n}`:"";return`${e}${t.substr(r.length)}${o}`}return!1}async function h(e,t){try{const a=t?`${t}=1`:"",n=await fetch(`${e}robots.txt${a?`?${a}`:""}`);if(!n.ok)return!1;const r=f(e,(await n.text()).match(/^sitemap:(.*)$/im)[1].trim(),a);if(r){const e=await fetch(r);if(!e.ok)return!1;const t=await e.text();return!!/<(?:sitemap|urlset)/gm.test(t)&&r}return!1}catch(e){return!1}}const g=["sitemap.xml","sitemap_index.xml","sitemap-index.xml","sitemap/","post-sitemap.xml","sitemap/sitemap.xml","sitemap/index.xml","sitemapindex.xml","sitemap.php","sitemap.txt","index.php/sitemap_index.xml","index.php?xml_sitemap=params=","glossar/sitemap.xml"];async function R(e){let t=arguments.length>1&&void 0!==arguments[1]?arguments[1]:g;const a=t.map((t=>fetch(`${e}${t}`,{mode:"no-cors"})));for(const e of a)try{const t=await e,a=await t.text();if(a.indexOf("<sitemapindex")>-1||a.indexOf("<urlset")>-1)return t.url}catch(e){}return!1}const y="https://base";async function E(e,t,a){if(a)try{const n=await async function(e){const t=await fetch(e),a=await t.text();return(new DOMParser).parseFromString(a.trim(),"application/xml")}(t),{protocol:r}=new URL(t,y),c=n.querySelector("sitemapindex");if(c){const t=Array.from(c.children).map((e=>{var t;return null===(t=e.querySelector("loc"))||void 0===t?void 0:t.textContent})).filter(Boolean);for(const n of t){const t=f(e,n)||n;await E(e,t,a)}}const o=n.querySelector("urlset");if(o){const e=Array.from(o.children).map((e=>{var t;return null===(t=e.querySelector("loc"))||void 0===t?void 0:t.textContent})).filter(Boolean).map((e=>{try{const t=new URL(e,y);return"http:"===t.protocol&&(t.protocol=r),t.toString()}catch(t){return e}}));a.push(...e)}}catch(e){console.error(`Error occurred during "crawl('${t}')":\n\r Error: ${e}`)}else try{return(await E(e,t,[])).sort(((e,t)=>e.length-t.length))}catch(e){return console.error(e),[]}return a}async function b(e){const t=(0,s.__)('If you think a sitemap exists but you get this error, please <a href="%s" target="_blank" />contact our support</a> and we will look individually where the problem is in your WordPress installation.',(0,s.__)("https://devowl.io/support/"));let a=window.realCookieBannerQueue.originalHomeUrl;a=a.split("?",2)[0],null==e||e("find-sitemap");const n=await function(e,t,a){return new Promise(((n,r)=>{const c=e=>setTimeout((()=>n(e)),100);(async()=>{try{const n=await h(e);if(n)return void c(n);const r=await R(e);if(r)return void c(r);{const a=await h(e,t);if(a)return void c(a)}if(a){const t=await R(e,a);if(t)return void c(t)}c(!1)}catch(e){r(e)}})()}))}(a,"rcb-force-sitemap",["?sitemap=index&rcb-force-sitemap=1"]);if(!1===n)throw new Error(`${(0,s.__)("We didn't find a sitemap on your website. We need it to scan all the subpages of your website. Do you have this feature disabled in your WordPress?")} ${t}`);null==e||e("collect-sitemap");let r=[];try{r=await E(a,n)}catch(e){throw new Error(`${(0,s.__)("The sitemap could not be parsed. Therefore, we cannot check for services on your website.")} ${t}`)}if(0===r.length)throw new Error(`${(0,s.__)("The sitemap is empty. So, we could not add any URLs to the scanner.")} ${t}`);return r}var _=a(61959),k=a(21850),w=a(46270);const v={icon:{tag:"svg",attrs:{viewBox:"64 64 896 896",focusable:"false"},children:[{tag:"path",attrs:{d:"M464 688a48 48 0 1096 0 48 48 0 10-96 0zm72-112c4.4 0 8-3.6 8-8V296c0-4.4-3.6-8-8-8h-48c-4.4 0-8 3.6-8 8v272c0 4.4 3.6 8 8 8h48zm400-188h-59.3c-2.6 0-5 1.2-6.5 3.3L763.7 538.1l-49.9-68.8a7.92 7.92 0 00-6.5-3.3H648c-6.5 0-10.3 7.4-6.5 12.7l109.2 150.7a16.1 16.1 0 0026 0l165.8-228.7c3.8-5.3 0-12.7-6.5-12.7zm-44 306h-64.2c-5.5 0-10.6 2.9-13.6 7.5a352.2 352.2 0 01-49.8 62.2A355.92 355.92 0 01651.1 840a355 355 0 01-138.7 27.9c-48.1 0-94.8-9.4-138.7-27.9a355.92 355.92 0 01-113.3-76.3A353.06 353.06 0 01184 650.5c-18.6-43.8-28-90.5-28-138.5s9.4-94.7 28-138.5c17.9-42.4 43.6-80.5 76.4-113.2 32.8-32.7 70.9-58.4 113.3-76.3a355 355 0 01138.7-27.9c48.1 0 94.8 9.4 138.7 27.9 42.4 17.9 80.5 43.6 113.3 76.3 19 19 35.6 39.8 49.8 62.2 2.9 4.7 8.1 7.5 13.6 7.5H892c6 0 9.8-6.3 7.2-11.6C828.8 178.5 684.7 82 517.7 80 278.9 77.2 80.5 272.5 80 511.2 79.5 750.1 273.3 944 512.4 944c169.2 0 315.6-97 386.7-238.4A8 8 0 00892 694z"}}]},name:"issues-close",theme:"outlined"};var x=a(51928),C=function(e,t){return n.createElement(x.Z,(0,w.Z)({},e,{ref:t,icon:v}))};const S=n.forwardRef(C);var T=a(71414);const $=(0,r.Pi)((()=>{const{optionStore:{others:{isPro:e}},scannerStore:{canShowResults:t,foundScanResultsCount:a,needsAttentionCount:r}}=(0,u.m)(),[p,f]=(0,n.useState)(void 0),{status:h,currentJob:g,total:R,percent:y,remaining:E,cancelling:w,handleStart:v,handleCancel:x,step:C,stepText:$}=function(e,t){const{modal:a}=d.Z.useApp(),[r,c]=(0,n.useState)("idle"),{remaining:o,...l}=(0,m.p)(e,"rcb-scan-list",(0,n.useCallback)((()=>{c("idle")}),[])),{scannerStore:p,checklistStore:f}=(0,u.m)(),h=(0,n.useCallback)((async()=>{try{const e=await b(c);c("add-to-queue"),await p.addUrlsToQueue({urls:e,purgeUnused:!0}),await Promise.all([p.fetchResultExternals(),p.fetchResultTemplates(),(0,i.refreshQueue)()]),c("done"),f.fetchChecklist()}catch(e){e instanceof Error&&a.error({title:(0,s.__)("Whoops!"),content:React.createElement("span",{dangerouslySetInnerHTML:{__html:e.message}})}),c("idle")}}),[]);(0,n.useEffect)((()=>{(0,i.fetchStatus)(!0)}),[]);const g=(0,n.useMemo)((()=>{switch(r){case"idle":return(0,s.__)("Scan complete website");case"find-sitemap":return(0,s.__)("Find your website sitemap...");case"collect-sitemap":return(0,s.__)("Collect all scannable URLs...");case"add-to-queue":case"done":return(0,s.__)("Add URLs to queue...")}return""}),[r]);return{handleStart:h,step:r,stepText:g,setStep:c,remaining:o,...l}}(p),Z="idle"!==C,{isLicensed:U,modal:A,open:I}=(0,T.T)({title:(0,s.__)("Scanner requires up-to-date search data from the Service Cloud"),description:(0,s.__)("The scanner can automatically search your website for used services. To do this, it needs an up-to-date database of search data for services, which must be downloaded from Real Cookie Banner's Service Cloud. The data will be downloaded locally to your server, so your website visitors will not need to connect to the cloud.")+(e?"":` ${(0,s.__)("You can activate your free licence at any time, without any costs.")}`),mode:"license-activation",feature:"scanner",assetName:"service-cloud.svg",assetMaxHeight:200}),N=(0,n.useCallback)((()=>{U?v():I()}),[v,U,I]),{start:P}=(0,_.y)();return(0,n.useEffect)((()=>{P&&N()}),[]),(0,n.useEffect)((()=>{f(E>0||"done"===C?5e3:void 0)}),[E,C]),void 0===E?React.createElement(c.Z,{style:{display:"block"}}):React.createElement("div",{className:"wp-clearfix"},A,t&&React.createElement("div",{style:{float:"left",margin:"5px 10px"}},0===r?React.createElement(React.Fragment,null,React.createElement(k.Z,{twoToneColor:"#52c41a"})," ",(0,s.__)("All recommendations applied")):React.createElement(React.Fragment,null,React.createElement(S,{style:{color:"#dba617"}})," ",(0,s.__)("%d of %d recommendations applied",a-r,a))),"failed"===h?React.createElement(o.Z,{style:{clear:"both"},description:(0,s.__)("Scan failed"),image:React.createElement(l.Z,{type:"circle",width:100,percent:100,status:"exception"})}):"done"===h?React.createElement(o.Z,{style:{clear:"both"},description:(0,s.__)("Scan completed"),image:React.createElement(l.Z,{type:"circle",width:100,percent:100})}):E>0&&g&&R?React.createElement(o.Z,{style:{clear:"both"},description:React.createElement(React.Fragment,null,React.createElement("div",null,(0,s._i)((0,s.__)("Currently scanning {{code}}%s{{/code}} (%d of %d pages scanned)...",g.data.url,R-E,R),{code:React.createElement("code",null)})),React.createElement("div",{className:"notice notice-info inline below-h2 notice-alt",style:{margin:"10px 0 0 0",display:"inline-block"}},React.createElement("p",null,(0,s.__)("You can add already found services, edit your website or have a coffee in the meantime. The scanner runs in the background.")))),image:React.createElement(l.Z,{type:"circle",width:100,percent:y})},React.createElement("button",{className:"button button-primary",onClick:x,disabled:w},(0,s.__)("Cancel scan"))):t?React.createElement("button",{id:"rcb-btn-scan-start",className:"button button-primary alignright",onClick:N,disabled:Z,style:{marginBottom:10,display:"done"!==C?void 0:"none"}},$):React.createElement(o.Z,{description:(0,s.__)("Scan your website for services that may set cookies or process personal data to obtain consent.")},React.createElement("button",{className:"button button-primary",onClick:N,disabled:Z},$)))}));var Z=a(45567),U=a(43734),A=a(61415),I=a(70756),N=a(76644),P=a(35392),L=a(69017),D=a(98595),B=a(14195),O=a(70698),F=a(24635),H=a(47620),W=a(15998),M=a.n(W);const Y=(0,r.Pi)((e=>{let{record:t}=e;const{message:a}=d.Z.useApp(),[r,o]=(0,n.useState)(!1),{data:{id:l},markup:u,store:m}=t,p=(0,n.useMemo)((()=>u?{...window.cm_settings,codemirror:{...M().codeEditor.defaultSettings.codemirror,mode:u.mime,lint:!1,readOnly:!0}}:{}),[u]),f=(0,n.useCallback)((()=>{r?o(!1):(o(!0),m.fetchMarkup(l))}),[r]),h=(0,n.useCallback)((async()=>{m.addUrlsToQueue({urls:[t.data.sourceUrl],purgeUnused:!1}),Promise.all([m.fetchResultExternals(),m.fetchResultTemplates(),(0,i.refreshQueue)()]),a.info((0,s.__)("Page is scheduled for scanning again..."))}),[]);return React.createElement(React.Fragment,null,React.createElement(O.Z,{title:(0,s.__)("Element found by markup"),open:r,width:700,bodyStyle:{paddingBottom:0},okButtonProps:{style:{display:"none"}},onCancel:f,cancelText:(0,s.__)("Close")},React.createElement(c.Z,{spinning:!u},u&&React.createElement(H.Y,{settings:p,value:u.markup}))),React.createElement(F.Z.Button,{onClick:f,menu:{items:[{key:"scan-again",onClick:h,label:(0,s.__)("Scan again")}]}},(0,s.__)("Open markup")))})),{Column:q}=I.Z,Q=(0,r.Pi)((e=>{let{instance:t,style:a,reloadDependencies:r=[],reload:c=!0}=e;const{scannerStore:l}=(0,u.m)(),{identifier:i,busy:d}=t,p=l.resultAllExternalUrls.get(i),f=(0,n.useCallback)((e=>{let{data:{id:t}}=e;return t}),[]),{remaining:h}=(0,m.p)(),[g,R]=(0,n.useState)("");return(0,n.useEffect)((()=>{!async function(){if(c)try{await l.fetchResultAllExternals(t),R("")}catch(t){var e;R((null===(e=t.responseJSON)||void 0===e?void 0:e.message)||t.message)}}()}),[t,c,...r]),React.createElement(React.Fragment,null,h>0&&React.createElement("div",{className:"notice notice-info below-h2 notice-alt",style:{margin:"0 0 10px"}},React.createElement("p",null,(0,s.__)("Since the scanner is currently still running, the table may be refreshed."))),React.createElement(I.Z,{locale:{emptyText:g?React.createElement(N.ZP,{title:(0,s.__)("Something went wrong."),subTitle:g,status:"500"}):React.createElement(o.Z,{description:(0,s.__)("No external URL found")})},dataSource:Array.from(p?p.values():[]),rowKey:f,size:"small",bordered:!0,style:a,loading:(!p||0===p.size)&&d},React.createElement(q,{title:(0,s.__)("Last scanned"),defaultSortOrder:"descend",sorter:(e,t)=>new Date(e.data.lastScanned).getTime()-new Date(t.data.lastScanned).getTime(),dataIndex:["data","lastScanned"],key:"created",render:(e,t)=>{let{data:{lastScanned:a}}=t;return new Date(a).toLocaleString(document.documentElement.lang)}}),React.createElement(q,{title:(0,s.__)("HTML Tag"),dataIndex:["data","tag"],key:"tag",render:(e,t)=>{let{data:{tag:a}}=t;return React.createElement(P.Z,null,`<${a} />`)}}),React.createElement(q,{title:React.createElement(L.Z,{title:(0,s.__)("This status shows you if the URL was blocked by a content blocker you defined.")},React.createElement("span",null,(0,s.__)("Blocked?")," ",React.createElement(D.Z,{style:{color:"#9a9a9a"}}))),sorter:(e,t)=>e.data.blocked===t.data.blocked?0:e.data.blocked?-1:1,dataIndex:["data","blocked"],key:"blocked",render:(e,t)=>{let{data:{blocked:a}}=t;return a?React.createElement(k.Z,{twoToneColor:"#52c41a"}):React.createElement(B.Z,{twoToneColor:"#eb2f96"})}}),React.createElement(q,{title:(0,s.__)("Blocked URL"),dataIndex:["data","blockedUrl"],key:"blockedUrl",render:(e,t)=>{let{blockedUrlTruncate:a,data:{blockedUrl:n}}=t;return React.createElement("a",{href:n,target:"_blank",rel:"noreferrer",title:n},a)}}),React.createElement(q,{title:(0,s.__)("Found on this URL"),sorter:(e,t)=>e.data.sourceUrl.localeCompare(t.data.sourceUrl),dataIndex:["data","sourceUrl"],key:"sourceUrl",render:(e,t)=>{let{sourceUrlTruncate:a,data:{sourceUrl:n}}=t;return React.createElement("a",{href:n,target:"_blank",rel:"noreferrer"},a)}}),React.createElement(q,{title:(0,s.__)("Actions"),dataIndex:["data","markup"],key:"markup",render:(e,t)=>t.data.markup?React.createElement(Y,{record:t}):""})))})),J=(0,r.Pi)((e=>{let{template:t,onVisibleChange:a}=e;const{message:r}=d.Z.useApp(),{scannerStore:c}=(0,u.m)(),o=[],{data:{identifier:l,name:m,consumerData:{scan:p}}}=t,[f,h]=(0,n.useState)(!1),g=(0,n.useCallback)((()=>{null==a||a(!f),h(!f)}),[l,f]),R=(0,n.useCallback)((async()=>{const e=c.resultAllExternalUrls.get(l),t=Array.from(e?e.values():[]);for(const e of t)o.push(e.data.sourceUrl);await c.addUrlsToQueue({urls:o,purgeUnused:!1}),r.info((0,s.__)("Pages are scheduled for scanning again...")),await Promise.all([c.fetchResultExternals(),c.fetchResultTemplates(),(0,i.refreshQueue)()])}),[]),y=!1===p?0:p.foundOnSitesCount;return React.createElement(React.Fragment,null,React.createElement(O.Z,{title:m,open:f,width:1400,bodyStyle:{paddingBottom:0},onCancel:g,cancelText:(0,s.__)("Close"),cancelButtonProps:{style:{float:"right",marginLeft:"10px"}},okButtonProps:{type:"default"},onOk:R,okText:(0,s.__)("Scan these pages again")},React.createElement(Q,{instance:t,reload:f&&y>0,reloadDependencies:[f,y]})),React.createElement("a",{onClick:e=>{e.preventDefault(),e.stopPropagation(),g()}},(0,s._n)("On %d page","On %d pages",y,y)))})),z=(0,r.Pi)((()=>{const[e,t]=(0,n.useState)(!1),{scannerStore:a,cookieStore:r,optionStore:{isTcf:o}}=(0,u.m)(),{sortedTemplates:l,resultTemplates:i,busyResultTemplates:d}=a,{remaining:p}=(0,m.p)(),{essentialGroup:f}=r,[h,g]=(0,n.useState)(!1),R=(0,n.useCallback)((async n=>{if(h||!n||e)return;t(!0);const{identifier:r,isHidden:c,name:l}=n,s=a.resultTemplates.get(r),{type:i,templateModel:u}=s,d=`navigateAfterCreation=${encodeURIComponent("#scanner")}`;if("service"===i)setTimeout((()=>window.location.href=`#/cookies/${null==f?void 0:f.key}/new?force=${r}&${d}`),0);else{const e=u;await e.fetchUse();const{use:{consumerData:{serviceTemplates:t,createAdNetwork:a}}}=e,n=t.filter((e=>{let{identifier:t}=e;return t===r}))[0]||t[0];let s="";if(n)if(!n.consumerData.isCreated||c){if(c)s=`#/cookies/${null==f?void 0:f.key}/new?force=${r}&${d}`;else if(!n.consumerData.isCreated){var m;s=`#/cookies/${null==f?void 0:f.key}/new?force=${n.identifier}&attributes=${JSON.stringify({createContentBlocker:(null===(m=n.group)||void 0===m?void 0:m.toLowerCase())!==(null==f?void 0:f.data.name.toLowerCase()),createContentBlockerId:r})}&${d}`}}else s=`#/blocker/new?force=${r}&${d}`;else a&&(s=o?`#/cookies/tcf-vendors/new?adNetwork=${encodeURIComponent(a)}`:`#/settings/tcf?tcfIntegrationItem=${encodeURIComponent(l)}&navigateAfterTcfActivation=${encodeURIComponent(`#/cookies/tcf-vendors/new?adNetwork=${encodeURIComponent(a)}`)}`);s&&setTimeout((()=>window.location.href=s),0)}t(!1)}),[e,h,f]);return React.createElement(Z.f,{style:{textAlign:"center"}},React.createElement(U.Z,null,(0,s.__)("Services, for which you should obtain consent")),React.createElement(c.Z,{spinning:d&&!p||e},React.createElement(A.G,{showHidden:!0,grayOutAlreadyExisting:!0,templates:l.map((e=>{let{data:t}=e;return t})),onSelect:R,renderActions:(0,n.useCallback)(((e,t)=>{const a=e,{tcfVendorIds:n,name:r,consumerData:{scan:c,isDisabled:l,createAdNetwork:u}}=a;return[(null==n?void 0:n.length)>0&&!o?React.createElement("a",{key:"activate-tcf",href:`#/settings/tcf?tcfIntegrationItem=${encodeURIComponent(r)}&navigateAfterTcfActivation=${encodeURIComponent(u?`#/cookies/tcf-vendors/new?adNetwork=${encodeURIComponent(u)}`:"#/scanner")}`},(0,s.__)("Activate TCF")):l?void 0:React.createElement("a",{key:"create-now",onClick:t},(0,s.__)("Create now")),c&&React.createElement(J,{template:i.get(e.identifier),key:"table",onVisibleChange:g})].filter(Boolean)}),[])})))}));var V=a(61811),G=a(57853);const j=e=>{let{count:t}=e;const a=(0,n.useMemo)((()=>{const e=[];for(let a=0;a<t;a++)e.push({key:a});return e}),[t]);return React.createElement(V.Z,{dataSource:a,renderItem:()=>React.createElement(V.Z.Item,null,React.createElement(G.Z,{loading:!0,active:!0,avatar:!1,paragraph:{rows:1}}))})};var K=a(89596),X=a(44507),ee=a(93404),te=a(68384);const ae=(0,r.Pi)((e=>{let{item:t}=e;const[a,r]=(0,n.useState)(!1),{cookieStore:{essentialGroup:c}}=(0,u.m)(),{openDialog:o}=(0,te.u)(),{data:{host:l}}=t,{addLink:i}=(0,ee.w)(),d=(0,n.useCallback)((()=>{r(!a)}),[a]),m=(0,n.useCallback)((()=>{r(!1),o()}),[o]),p=`navigateAfterCreation=${encodeURIComponent(window.location.href)}`;return React.createElement(X.Z,{open:a,content:React.createElement(React.Fragment,null,React.createElement("p",null,React.createElement("strong",null,(0,s._i)((0,s.__)("Does {{i}}%s{{/i}} belong to an essential service without which your website would technically no longer work?",l),{i:React.createElement("i",null)}))),React.createElement("p",null,React.createElement("a",{className:"button button-primary",href:`${i}?force=scratch&attributes=${JSON.stringify({rules:`*${l}*`})}&${p}`},(0,s.__)("No"))," ",React.createElement("a",{className:"button",href:`#/cookies/${null==c?void 0:c.key}/new?force=scratch&${p}`},(0,s.__)("Yes"))," ",React.createElement("button",{className:"button",onClick:m},(0,s.__)("I do not know"))),React.createElement("p",{className:"description"},(0,s._i)((0,s.__)("{{strong}}No:{{/strong}} Non-essential services that process personal data (e.g. IP address in some countries) or set cookies may only be loaded after consent. You should block them using a content blocker until consent is given. {{i}}Examples would be iframes, YouTube and similar embeddings or tracking tools.{{/i}}"),{strong:React.createElement("strong",null),i:React.createElement("i",null)})),React.createElement("p",{className:"description"},(0,s._i)((0,s.__)("{{strong}}Yes (rare cases):{{/strong}} You should inform your users about the use of the service in the essential services group. You do not need to create a content blocker, as the service can be loaded without consent. {{i}}Examples are privacy-friendly spam protection tools or security tools.{{/i}}"),{strong:React.createElement("strong",null),i:React.createElement("i",null)})),React.createElement("p",null,React.createElement("button",{className:"button",onClick:d},(0,s.__)("Close")))),placement:"right",overlayStyle:{maxWidth:350}},React.createElement("a",{onClick:d},(0,s.__)("Handle external URL")))})),ne=(0,r.Pi)((e=>{let{item:t}=e;const[a,r]=(0,n.useState)(!1),{data:{host:o,foundOnSitesCount:l,tags:d,ignored:m},inactive:p,blockedStatus:f,blockedStatusText:h,busy:g}=t,{scannerStore:R}=(0,u.m)(),y=(0,n.useCallback)((()=>{r(!a)}),[o,a]),E=(0,n.useCallback)((async()=>{const e=[];try{await R.fetchResultAllExternals(t),R.resultAllExternalUrls.get(t.data.host).forEach((t=>{e.push(t.data.sourceUrl)})),await R.addUrlsToQueue({urls:e,purgeUnused:!1}),await Promise.all([R.fetchResultExternals(),(0,i.refreshQueue)()])}catch(e){e instanceof Error&&console.log(e)}}),[t]);return React.createElement(React.Fragment,null,React.createElement(V.Z.Item,{itemID:o,actions:[React.createElement(ae,{key:"handle",item:t}),m&&React.createElement(K.Z,{key:"delete",title:(0,s.__)("Are you sure that you want to restore this entry?"),placement:"bottomRight",onConfirm:()=>{t.ignore(!1),r(!1)},okText:(0,s.__)("Restore"),cancelText:(0,s.__)("Cancel"),overlayStyle:{maxWidth:350}},React.createElement("a",null,(0,s.__)("Restore"))),!p&&React.createElement(K.Z,{key:"delete",title:(0,s.__)("Are you sure that you want to ignore this entry?"),placement:"bottomRight",onConfirm:()=>{t.ignore(!0),r(!1)},okText:(0,s.__)("Ignore"),cancelText:(0,s.__)("Cancel"),overlayStyle:{maxWidth:350}},React.createElement("a",null,(0,s.__)("Ignore"))),React.createElement(React.Fragment,null,React.createElement("a",{onClick:E},(0,s.__)("Scan these pages again")))].filter(Boolean),style:{opacity:p&&!a?.6:1}},React.createElement(c.Z,{spinning:g},React.createElement(V.Z.Item.Meta,{title:React.createElement("span",null,o," ",d.map((e=>React.createElement(P.Z,{key:e},`<${e} />`))),m&&React.createElement(P.Z,null,(0,s.__)("Ignored")),"none"!==f&&React.createElement(P.Z,{color:"partial"===f?"warning":"success"},h)),description:React.createElement("button",{className:"button-link",onClick:y},a?(0,s.__)("Close"):(0,s._n)("Embeds found on %d page","Embeds found on %d pages",l,l))}))),a&&React.createElement(Q,{instance:t,reload:a&&l>0,reloadDependencies:[a,l],style:{marginTop:"-6px"}}))}));var re=a(36157),ce=a(4213),oe=a(50088);const le=(0,r.Pi)((()=>{const{scannerStore:e,cookieStore:t}=(0,u.m)(),{currentJob:a}=(0,m.p)(),{templatesCount:r,externalUrlsCount:c,busyExternalUrls:o,sortedExternalUrls:l,canShowResults:i}=e,d=(0,re.v)("scanner");return(0,n.useEffect)((()=>{e.fetchResultTemplates(),e.fetchResultExternals(),t.fetchGroups()}),[]),React.createElement(React.Fragment,null,React.createElement(ce.X,{style:{margin:"10px 0 0 0"}}),React.createElement(Z.f,null,React.createElement($,null),i&&r>0&&React.createElement(z,null),i&&c>0&&(o&&void 0===a?React.createElement(j,{count:c}):React.createElement("div",null,React.createElement(U.Z,null,(0,s.__)("Embeds of external URLs to be checked")),React.createElement("div",{style:{maxWidth:800,margin:"0px auto 20px",textAlign:"center"}},React.createElement("p",{className:"description"},(0,s.__)("You are embedding scripts, styles, iframes or similar from the following third-party servers. We currently do not have service templates for these. Therefore, you may have to create a service and/or content blocker yourself after you have assessed the situation."))),React.createElement(V.Z,null,l.map((e=>React.createElement(ne,{item:e,key:e.data.host})))))),React.createElement("div",{style:{maxWidth:800,margin:"30px auto 0",textAlign:"center"}},d)),React.createElement(oe.K,{identifier:"scanner",title:(0,s.__)("What does the scanner find?"),width:900}))}))},61959:(e,t,a)=>{a.d(t,{y:()=>c});var n=a(66711),r=a(8700);function c(){return r.qs.parse((0,n.useLocation)().search)}},93404:(e,t,a)=>{a.d(t,{w:()=>l});var n=a(66711),r=a(49048),c=a(87363),o=a(61278);const l=()=>{const e=(0,n.useParams)(),{cookieStore:t}=(0,r.m)(),a=+e.blocker,l=isNaN(+a)?0:+a,s=!!a,i=t.blockers.entries.get(l)||new o.p(t.blockers,{id:0}),u=(0,c.useCallback)((e=>{let{key:t}=e;return`#/blocker/edit/${t}`}),[i]);return{blocker:i,id:l,queried:s,fetched:0!==i.key,link:"#/blocker",editLink:u,addLink:"#/blocker/new"}}}}]);
//# sourceMappingURL=https://sourcemap.devowl.io/real-cookie-banner/4.4.1/9d8fef48b21ef9325aa493823a1b2d1c/chunk-config-tab-scanner.lite.js.map
