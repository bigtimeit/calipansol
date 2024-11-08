"use strict";(self.webpackChunkrealCookieBanner_=self.webpackChunkrealCookieBanner_||[]).push([[625],{78451:(e,t,a)=>{a.d(t,{x:()=>r});var n=a(71414);const r=e=>{let{children:t,wrapperAttributes:a={},...r}=e;const{modal:l,tag:c}=(0,n.T)(r);return React.createElement(React.Fragment,null,l,React.createElement("span",a,c))}},44839:(e,t,a)=>{a.d(t,{A:()=>n});const n=(0,a(68038).Pi)((()=>React.createElement("div",null)))},22724:(e,t,a)=>{a.r(t),a.d(t,{ConsentTabRouter:()=>se});var n=a(68038),r=a(66711),l=a(45567),c=a(6628),o=a(40045),s=a(87363);const i=(0,n.Pi)((()=>React.createElement("div",null))),_=(0,n.Pi)((()=>React.createElement("div",null)));var m=a(44839),d=a(49048),u=a(73447),p=a(45890),E=a(43734),R=a(24657),h=a(19893),g=a(88122),y=a(48488),b=a.n(y);function T(){return(0,s.useMemo)((()=>({[(0,o.__)("Today")]:[b()(),b()()],[(0,o.__)("This Year")]:[b()().startOf("year"),b()().endOf("year")],[(0,o.__)("This Month")]:[b()().startOf("month"),b()().endOf("month")],[(0,o.__)("This Week")]:[b()().startOf("week"),b()().endOf("week")],[(0,o.__)("Last 30 days")]:[b()().subtract(30,"days"),b()()],[(0,o.__)("Last 90 days")]:[b()().subtract(30,"days"),b()()]})),[])}var v=a(15764);const f=(0,n.Pi)((()=>{const{optionStore:{others:{isPro:e,assetsUrl:t},contexts:a},statsStore:n}=(0,d.m)(),{filters:{dates:r,context:l}}=n,c=Object.keys(a),[y,b]=(0,s.useState)(),f=T();return e?React.createElement(React.Fragment,null,React.createElement("div",{style:{textAlign:"right"}},c.length>1&&React.createElement(React.Fragment,null,React.createElement("label",null,React.createElement(p.Z,{style:{width:200,textAlign:"left"},value:l,onSelect:e=>n.applyContext(e)},c.map((e=>React.createElement(p.Z.Option,{value:e,key:e},a[e]))))),React.createElement(E.Z,{type:"vertical"})),React.createElement("label",null,(0,o.__)("Period"),":"," ",React.createElement(u.D,{value:r,ranges:f,onChange:e=>n.applyDates(e)}))),2===(null==r?void 0:r.length)?React.createElement(React.Fragment,null,React.createElement(R.Z,null,React.createElement(h.Z,{md:12,sm:24},React.createElement(E.Z,null,(0,o.__)("Consents by clicked button")),React.createElement(i,null)),React.createElement(h.Z,{md:12,sm:24},React.createElement(E.Z,null,(0,o.__)("Cookie banner bypass")),React.createElement(_,null))),React.createElement(R.Z,null,React.createElement(h.Z,{md:20,sm:24,style:{margin:"auto",paddingTop:20,marginTop:30}},React.createElement(E.Z,null,(0,o.__)("Consents by group")),React.createElement(m.A,null)))):React.createElement(g.Z,{description:(0,o.__)("Please provide a date range!")})):React.createElement(React.Fragment,null,React.createElement(v.n,{title:(0,o.__)("Want to see detailed statistics about the consents of your visitors?"),inContainer:!0,inContainerElement:y,testDrive:!0,feature:"stats",description:(0,o.__)("You can get several statistics about how your users use the cookie banner. This helps you to calculate the total number of users who do not want to be tracked, for example, by extrapolating data from Google Analytics.")}),React.createElement("div",{ref:b,className:"rcb-antd-modal-mount",style:{height:800,backgroundImage:`url('${t}statistics-blured.png')`}}))}));var S=a(46270),k=a(70756),N=a(63593),w=a(35392),I=a(89596),C=a(69017),A=a(87642),Z=a(17635),x=a(98595);const O=(0,n.Pi)((e=>{let{record:t,visible:a,onClose:n,replayBannerRecord:r}=e})),P=(0,n.Pi)((e=>{let{record:{forwarded:t,revision:{data:{options:a}},revision_independent:{data:{options:n}},...r}}=e;const{optionStore:{others:{pageByIdUrl:l,iso3166OneAlpha2:c}}}=(0,d.m)(),s={...n,...a};return t?null:React.createElement(R.Z,null,React.createElement(h.Z,{span:24},React.createElement(E.Z,null,(0,o.__)("Settings at the time of consent"))),Object.keys(s).map((e=>{var t,a,n;let i=e,_=s[e],m=!0;switch(e){case"SETTING_TCF_SCOPE_OF_CONSENT":case"SETTING_TCF":case"SETTING_PRIVACY_POLICY_EXTERNAL_URL":case"SETTING_PRIVACY_POLICY_IS_EXTERNAL_URL":case"SETTING_IMPRINT_EXTERNAL_URL":case"SETTING_IMPRINT_IS_EXTERNAL_URL":case"SETTING_COOKIE_VERSION":case"SETTING_CONSENT_DURATION":case"SETTING_GCM_ENABLED":case"SETTING_GCM_SHOW_RECOMMONDATIONS_WITHOUT_CONSENT":case"SETTING_GCM_REDACT_DATA_WITHOUT_CONSENT":case"SETTING_GCM_LIST_PURPOSES":case"SETTING_GCM_ADDITIONAL_URL_PARAMETERS":return null;case"SETTING_TCF_PUBLISHER_CC":if(!s.SETTING_TCF)return null;i=(0,o.__)("Country of the website operator"),_=c[_]||_;break;case"SETTING_OPERATOR_COUNTRY":i=(0,o.__)("Website operator country"),_=c[_]||_||(0,o.__)("Not defined");break;case"SETTING_OPERATOR_CONTACT_ADDRESS":i=(0,o.__)("Website operator full address"),_=_||(0,o.__)("Not defined");break;case"SETTING_OPERATOR_CONTACT_EMAIL":i=(0,o.__)("Website operator email"),_=_||(0,o.__)("Not defined");break;case"SETTING_OPERATOR_CONTACT_PHONE":i=(0,o.__)("Website operator phone"),_=_||(0,o.__)("Not defined");break;case"SETTING_TERRITORIAL_LEGAL_BASIS":i=(0,o.__)("Legal basis to be applied"),_=_.split(",").filter(Boolean).map((e=>{switch(e){case"gdpr-eprivacy":return(0,o.__)("GDPR / ePrivacy Directive");case"dsg-switzerland":return(0,o.__)("DSG (Switzerland)");default:return e}})).join(", ");break;case"SETTING_BANNER_ACTIVE":i=(0,o.__)("Cookie Banner/Dialog"),_=_?(0,o.__)("Active"):(0,o.__)("Deactivated");break;case"SETTING_BLOCKER_ACTIVE":i=(0,o.__)("Content Blocker"),_=_?(0,o.__)("Active"):(0,o.__)("Deactivated");break;case"SETTING_IMPRINT_ID":i=(0,o.__)("Legal notice page"),_=r.url_imprint?React.createElement("a",{href:r.url_imprint,target:"_blank",rel:"noopener noreferrer"},(0,o.__)("Open site")):(0,o.__)("Not defined");break;case"SETTING_PRIVACY_POLICY_ID":i=(0,o.__)("Privacy policy page"),_=r.url_privacy_policy?React.createElement("a",{href:r.url_privacy_policy,target:"_blank",rel:"noopener noreferrer"},(0,o.__)("Open site")):(0,o.__)("Not defined");break;case"SETTING_OPERATOR_CONTACT_FORM_ID":i=(0,o.__)("Contact form page"),_=_?React.createElement("a",{href:`${l}=${_}`,target:"_blank",rel:"noopener noreferrer",style:{marginRight:5}},(0,o.__)("Open site")," (ID ",_,")"):(0,o.__)("Not defined");break;case"SETTING_SET_COOKIES_VIA_MANAGER":i=(0,o.__)("Set cookies after consent via"),_="googleTagManager"===_||"googleTagManagerWithGcm"===_?"Google Tag Manager":"matomoTagManager"===_?"Matomo Tag Manager":(0,o.__)("Disabled");break;case"SETTING_ACCEPT_ALL_FOR_BOTS":i=(0,o.__)("Automatically accept all services for bots"),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_RESPECT_DO_NOT_TRACK":i=(0,o.__)('Respect "Do Not Track"'),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_COOKIE_DURATION":i=(0,o.__)("Duration of cookie consent"),_=`${_} ${(0,o.__)("days")}`;break;case"SETTING_SAVE_IP":i=(0,o.__)("Save IP address on consent"),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_EPRIVACY_USA":i=(0,o.__)("Consent for data processing in the USA"),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES":i=(0,o.__)("Consent for data processing in unsecure third countries"),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES_SAFE_COUNTRIES":if(!s.SETTING_DATA_PROCESSING_IN_UNSAFE_COUNTRIES)return null;if(i=(0,o.__)("Secure countries in terms of the GDPR"),_){const e=_.split(",").map((e=>c[e]));_=React.createElement(C.Z,{title:e.join(", ")},React.createElement(w.Z,null,(0,o.__)("%d countries",e.length)))}else _=(0,o.__)("Not defined");break;case"SETTING_AGE_NOTICE":i=(0,o.__)("Age notice for consent"),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_AGE_NOTICE_AGE_LIMIT":if(!s.SETTING_AGE_NOTICE)return null;i=(0,o.__)("Age limit"),_="INHERIT"===_?(0,o.__)("Determine age limit based on specified website operator country"):"GDPR"===_?(0,o.__)("GDPR standard"):c[_];break;case"SETTING_LIST_SERVICES_NOTICE":i=(0,o.__)("Naming of all services in first view"),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_CONSENT_FORWARDING":i=(0,o.__)("Consent Forwarding"),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_FORWARD_TO":i=(0,o.__)("Forward To"),_&&(_=_.split("|").filter(Boolean).map((e=>React.createElement("a",{key:e,href:e,target:"_blank",rel:"noopener noreferrer",style:{marginRight:5}},(0,o.__)("Open endpoint"))))),_=null!==(t=_)&&void 0!==t&&t.length?_:(0,o.__)("Not defined");break;case"SETTING_CROSS_DOMAINS":i=(0,o.__)("Additional cross domain endpoints"),_&&(_=_.split("\n").filter(Boolean).map((e=>React.createElement("a",{key:e,href:e,target:"_blank",rel:"noopener noreferrer",style:{marginRight:5}},(0,o.__)("Open endpoint"))))),_=null!==(a=_)&&void 0!==a&&a.length?_:(0,o.__)("Not defined");break;case"SETTING_HIDE_PAGE_IDS":i=(0,o.__)("Hide on additional pages"),_&&(_=_.split(",").filter(Boolean).map((e=>React.createElement("a",{key:e,href:`${l}=${e}`,target:"_blank",rel:"noopener noreferrer",style:{marginRight:5}},(0,o.__)("Open site")," (ID ",e,")")))),_=null!==(n=_)&&void 0!==n&&n.length?_:(0,o.__)("Not defined");break;case"SETTING_COUNTRY_BYPASS_ACTIVE":i=(0,o.__)("Geo-restriction"),_=_?(0,o.__)("Enabled"):(0,o.__)("Disabled");break;case"SETTING_COUNTRY_BYPASS_COUNTRIES":if(!s.SETTING_COUNTRY_BYPASS_ACTIVE)return null;if(i=(0,o.__)("Show banner only to users from these countries"),_){const e=_.split(",").map((e=>c[e]));_=React.createElement(C.Z,{title:e.join(", ")},React.createElement(w.Z,null,(0,o.__)("%d countries",e.length)))}else _=(0,o.__)("Not defined");break;case"SETTING_COUNTRY_BYPASS_TYPE":if(!s.SETTING_COUNTRY_BYPASS_ACTIVE)return null;i=(0,o.__)("Implicit consent for users from third countries"),_="all"===_?(0,o.__)("Accept all"):(0,o.__)("Accept only essentials");break;default:m=!1}return React.createElement(h.Z,{key:e,md:12,sm:24},React.createElement("div",{style:{padding:"0 10px"}},m?React.createElement("strong",null,i):React.createElement("code",null,i),": ",_))})))})),D=(0,n.Pi)((e=>{let{record:t,replayBlockerRecord:a,replayFinished:n}=e}));var G=a(78451);const F=(0,n.Pi)((e=>{let{record:t,onPreview:a}=e;const{optionStore:{others:{isPro:n}}}=(0,d.m)(),[r,l]=(0,s.useState)(!1),{custom_bypass:c,blocker:i,recorder:_,forwarded:m,viewed_page:u,design_version:p}=t;return c?React.createElement(React.Fragment,null,React.createElement(E.Z,null,(0,o.__)("Bypassed banner")),React.createElement("p",{className:"description"},(0,o._i)((0,o.__)("There is no preview for this consent, as it was given implicitly by {{strong}}%s bypass{{/strong}}.",t.custom_bypass_readable),{strong:React.createElement("strong",null)}))):i>0?React.createElement(React.Fragment,null,React.createElement(E.Z,null,(0,o.__)("Content Blocker as at the time of consent")),React.createElement("p",{className:"description"},(0,o.__)("The consent to the service was given via a content blocker. Below you can see how the content blocker looked like when the user consented.")),n?React.createElement(D,{record:t,replayBlockerRecord:r,replayFinished:()=>l(!1)}):React.createElement(G.x,{title:(0,o.__)("You want to see what was displayed to the visitor?"),tagText:(0,o.__)("Unlock preview"),testDrive:!0,feature:"consent-preview-blocker",assetName:(0,o.__)("pro-modal/consent-preview-blocker.png"),description:(0,o.__)("We generate the content blocker that your visitor has seen from all settings, design preferences and services. You can see exactly how his or her consent was obtained and track clicks on buttons.")}),n&&!!_&&React.createElement(React.Fragment,null,React.createElement("p",{className:"description",style:{marginTop:15}},(0,o.__)("The process of how the website visitor interacted with the content blocker to give consent was recorded for documentation purposes. You can replay the interactions. Note that the dimensions of the content blocker and consent dialog do not have to be the same as the ones that were displayed to the website visitor, and the recording may not be fully accurate if, for example, you use custom CSS on your website or the visitor used a translating browser extension.")),React.createElement("button",{className:"button-primary button-large",onClick:()=>{l(!r)}},r?(0,o.__)("Stop"):(0,o.__)("Replay interactions")))):m>0?React.createElement(React.Fragment,null,React.createElement(E.Z,null,(0,o.__)("Forwarded consent")),React.createElement("p",{className:"description"},(0,o._i)((0,o.__)("There is no preview for this consent, as it was given implicitly by forwarding the consent. The user has given his/her consent via {{a}}%s{{/a}}, and this consent has been forwarded automatically.",u),{a:React.createElement("a",{href:u,rel:"noopener noreferrer",target:"_blank"})}))):React.createElement(React.Fragment,null,React.createElement(E.Z,null,(0,o.__)("Cookie banner as at the time of consent")),React.createElement("p",{className:"description"},(0,o.__)("Use the button below to see what the cookie banner looked like at the time of the user's consent. The services/groups selected there also look the way the user saw them. A button framed in red shows which button the user clicked on at the time of consent.")),n?React.createElement("button",{className:"button-primary button-large",onClick:()=>{a(t,!1)},disabled:t.tcf_string&&p<7},(0,o.__)("Open banner")):React.createElement(G.x,{title:(0,o.__)("You want to see what was displayed to the visitor?"),tagText:(0,o.__)("Unlock preview"),testDrive:!0,feature:"consent-preview-banner",assetName:(0,o.__)("pro-modal/consent-preview-banner.png"),description:(0,o.__)("We generate the cookie banner from all (design) settings and services that the visitor has seen. You can see exactly how consent was obtained and track clicks on buttons.")}),n&&!!_&&React.createElement(React.Fragment,null,React.createElement("p",{className:"description",style:{marginTop:15}},(0,o.__)("The process of how the website visitor interacted with the cookie banner to give consent was recorded for documentation purposes. You can replay the interactions. Note that the recording may not be fully accurate if, for example, you use custom CSS on your website or the visitor used a translating browser extension.")),React.createElement("button",{className:"button-primary button-large",onClick:()=>{a(t,!0)},disabled:p<6||t.tcf_string&&p<7},(0,o.__)("Replay interactions")),p<6?React.createElement("div",{className:"notice notice-warning inline below-h2 notice-alt",style:{margin:"10px 0 0 0"}},React.createElement("p",null,(0,o._i)((0,o.__)("This consent was documented with a Real Cookie Banner version prior to 3.10.0. Due to rework to comply with the {{aAct}}European Accessibility Act{{/aAct}}, interactions with the currently installed version of Real Cookie Banner are no longer playable. However, they are still documented in the consent export. If you need to play the interactions, please request an old Real Cookie Banner version from the {{aSupport}}support{{/aSupport}}."),{aAct:React.createElement("a",{href:(0,o.__)("https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX:32019L0882"),rel:"noreferrer",target:"_blank"}),aSupport:React.createElement("a",{href:(0,o.__)("https://devowl.io/support/"),rel:"noreferrer",target:"_blank"})}))):t.tcf_string&&p<7?React.createElement("div",{className:"notice notice-warning inline below-h2 notice-alt",style:{margin:"10px 0 0 0"}},React.createElement("p",null,(0,o._i)((0,o.__)("This consent was documented with a Real Cookie Banner version prior to 4.1.0. Due to rework to comply with the TCF 2.2 standard, interactions with the currently installed version of Real Cookie Banner are no longer playable. However, they are still documented in the consent export. If you need to play the interactions, please request an old Real Cookie Banner version from the {{aSupport}}support{{/aSupport}}."),{aSupport:React.createElement("a",{href:(0,o.__)("https://devowl.io/support/"),rel:"noreferrer",target:"_blank"})}))):null))}));var L=a(80395);const Y=(0,n.Pi)((e=>{let{record:t}=e;const{message:a}=N.Z.useApp();return React.createElement(React.Fragment,null,React.createElement(E.Z,null,(0,o.__)("Export consent")),React.createElement("p",{className:"description"},(0,o.__)("Use the button below to export all consents in a machine readable form.")),React.createElement("button",{className:"button button-large",onClick:()=>{(0,L.v)(JSON.stringify(t.export)),a.success((0,o.__)("Successfully copied to the clipboard!"))}},(0,o.__)("Export to clipboard")))}));var B=a(29894);const U=(0,n.Pi)((e=>{let{record:t,onPreview:a}=e;const[n,r]=(0,s.useState)(!1),{viewed_page:l,context:c,viewport_width:i,viewport_height:_,forwarded:m}=t;return(0,s.useEffect)((()=>{t.fetchRevisions().then((()=>{r(!0)}))}),[t]),n?React.createElement("div",null,React.createElement("div",null,React.createElement("strong",null,(0,o.__)("Viewport (px)"),":")," ",i," x ",_),React.createElement("div",null,React.createElement("strong",null,(0,o.__)("Viewed page"),":")," ",React.createElement("a",{href:l,rel:"noopener noreferrer",target:"_blank"},l)),!!c&&React.createElement("div",null,React.createElement("strong",null,(0,o.__)("Context"),":")," ",React.createElement("code",null,c)),React.createElement(P,{record:t}),React.createElement(R.Z,null,React.createElement(h.Z,{md:m?void 0:12,sm:m?void 0:24,span:m?24:void 0},React.createElement("div",{style:{padding:10}},React.createElement(F,{record:t,onPreview:a}))),!m&&React.createElement(h.Z,{md:12,sm:24},React.createElement("div",{style:{padding:10}},React.createElement(Y,{record:t}))))):React.createElement(B.Z,{spinning:!0})}));var M=a(36157);const W=(0,n.Pi)((e=>{let{value:t,onChange:a}=e;const[n,r]=(0,s.useState)(t),[l,c]=(0,s.useState)(!1),{consentStore:i}=(0,d.m)(),{busyReferer:_,referer:m}=i;return(0,s.useEffect)((()=>{l&&i.fetchReferer()}),[l]),React.createElement(React.Fragment,null,React.createElement(p.Z,{showSearch:!0,onFocus:()=>c(!0),value:n,notFoundContent:_?React.createElement(B.Z,{size:"small"}):null,loading:_,dropdownMatchSelectWidth:!1,dropdownAlign:{points:["tr","br"]},style:{width:200},placeholder:(0,o.__)("Filter by URL..."),optionFilterProp:"children",onChange:e=>{const t=e||void 0;r(t),null==a||a(t)},className:"rcb-antd-select-ellipses-left"},React.createElement(p.Z.Option,{value:null,disabled:!t},(0,o.__)("Reset filter")),m.map((e=>React.createElement(p.Z.Option,{key:e,value:e},e)))))}));var V=a(30547),$=a(33124),K=a(60204);const{Column:H}=k.Z,{Column:j}=k.Z,z=(0,n.Pi)((()=>{const{message:e}=N.Z.useApp(),{consentStore:t,optionStore:{contexts:a}}=(0,d.m)(),{busyConsent:n,pageCollection:r,perPage:l,count:c,truncatedIpsCount:i,filters:{page:_,referer:m,context:R,dates:h,ip:y}}=t,[b,v]=(0,s.useState)(),[f,P]=(0,s.useState)(!1),[D,G]=(0,s.useState)(!0),F=Object.keys(a),L=T(),Y=(0,s.useCallback)((async()=>{try{await t.fetchAll()}catch(t){e.error(t.responseJSON.message)}}),[]);(0,s.useEffect)((()=>{Y()}),[]);const B=(0,M.v)("list-of-consents"),H=(0,M.v)("consents-deleted"),z=function(){const{__:e}=(0,K.Q)();return(0,s.useCallback)((()=>({filterDropdown:t=>{let{setSelectedKeys:a,selectedKeys:n,confirm:r,clearFilters:l}=t;return React.createElement("div",{style:{padding:8}},React.createElement($.Z,{autoFocus:!0,value:n[0],onChange:e=>a(e.target.value?[e.target.value]:[]),style:{width:188,marginBottom:8,display:"block"}}),React.createElement("button",{className:"button-primary right",style:{marginLeft:10},onClick:()=>r()},React.createElement(V.Z,null)," ",e("Search")),React.createElement("button",{className:"button right",onClick:l},e("Reset")),React.createElement("div",{className:"clear"}))},filterIcon:e=>React.createElement(V.Z,{style:{color:e?"#1890ff":void 0}})})),[])}();return React.createElement(React.Fragment,null,b&&React.createElement(O,{record:b,visible:D,replayBannerRecord:f,onClose:()=>{G(!1),P(!1),v(void 0)}}),React.createElement("div",{style:{textAlign:"right",marginBottom:15}},F.length>1&&React.createElement(React.Fragment,null,React.createElement("label",null,React.createElement(p.Z,{style:{width:200,textAlign:"left"},value:R,onSelect:e=>{t.applyPage(1),t.applyContext(e),Y()}},F.map((e=>React.createElement(p.Z.Option,{value:e,key:e},a[e]))))),React.createElement(E.Z,{type:"vertical"})),React.createElement("label",null,(0,o.__)("Period"),":"," ",React.createElement(u.D,{value:h,ranges:L,onChange:e=>{t.applyPage(1),t.applyDates(e||[void 0,void 0]),Y()}})),React.createElement(E.Z,{type:"vertical"}),React.createElement("label",{style:{textAlign:"left"}},React.createElement(W,{value:m,onChange:e=>{t.applyPage(1),t.applyReferer(e),Y()}}))),i>0&&!!y&&React.createElement("div",{className:"notice notice-warning inline below-h2 notice-alt",style:{margin:"10px 0"}},React.createElement("p",null,(0,o.__)("For data protection reasons, IP addresses, depending on the configuration of the cookie banner, were only shortened by the last octet and stored hashed. In this case, you can only assign consents to individual IP addresses with a high probability, but not with absolute certainty. Therefore, also match the time of consent to narrow down your search for the proper consent!"))),React.createElement(k.Z,{pagination:{current:_,pageSize:l,total:c,showTotal:(e,t)=>`${t[0]}-${t[1]} / ${e}`,showSizeChanger:!1},locale:{emptyText:React.createElement(g.Z,{description:(0,o.__)("No data")})},loading:n,dataSource:Array.from(r.values()),rowKey:"id",size:"small",bordered:!0,expandable:{expandedRowRender:e=>React.createElement("div",{style:{background:"white",padding:10}},React.createElement(U,{record:e,onPreview:(e,t)=>{v(e),P(t),G(!0)}})),rowExpandable:()=>!0,expandIcon:e=>{let{expanded:t,onExpand:a,record:n}=e;return React.createElement(w.Z,{style:{cursor:"pointer"},onClick:e=>a(n,e),icon:t?React.createElement(A.Z,null):React.createElement(Z.Z,null)},t?(0,o.__)("Less"):(0,o.__)("More"))}},onChange:(e,a)=>{var n,r;let{current:l}=e;const c=(null===(n=a.uuid)||void 0===n?void 0:n[0])||"",o=(null===(r=a.ip)||void 0===r?void 0:r[0])||"";t.applyPage(l),t.applyUuid(c),t.applyIp(o),Y()},footer:()=>React.createElement(I.Z,{overlayStyle:{maxWidth:300},arrow:{pointAtCenter:!0},title:(0,o._i)((0,o.__)("Are you sure you want to delete all consents? You should only do this if it is absolutely necessary. In case you want to continue, make sure you have {{a}}exported{{/a}} all consents beforehand (for legal reasons)."),{a:React.createElement("a",{href:"#/import"})}),okText:(0,o.__)("I am sure!"),cancelText:(0,o.__)("Cancel"),placement:"topRight",onConfirm:()=>window.confirm((0,o.__)("Just to be sure: Do you really want to delete all consents?"))&&t.deleteAll()},React.createElement("button",{className:"button-link"},(0,o.__)("Delete all consents")))},React.createElement(j,{title:(0,o.__)("Time of consent"),dataIndex:"created",key:"created",render:(e,t)=>{const{created:a}=t;return React.createElement(React.Fragment,null,new Date(a).toLocaleString(document.documentElement.lang)," ",t.dnt&&React.createElement(C.Z,{title:(0,o.__)("This consent was given automatically because the browser sent a 'Do Not Track' header. Accordingly, only essential services have been consented to.")},React.createElement(w.Z,{color:"magenta"},(0,o.__)("Do Not Track"))),t.blocker>0&&React.createElement(C.Z,{title:(0,o.__)("This consent has been given in a content blocker.")},React.createElement(w.Z,{color:"cyan"},(0,o.__)("Content Blocker"))),t.forwarded>0&&React.createElement(C.Z,{title:(0,o.__)("This consent was implicitly given by Consent Forwarding.")},React.createElement(w.Z,{color:"green"},t.forwarded_blocker?(0,o.__)("Forwarded through Content Blocker"):(0,o.__)("Forwarded"))),t.custom_bypass&&React.createElement(C.Z,{title:(0,o.__)("This consent was implicitly given by a configured bypass.")},React.createElement(w.Z,{color:"magenta"},t.custom_bypass_readable)))}}),React.createElement(j,(0,S.Z)({title:React.createElement(C.Z,{title:(0,o.__)("Unique name of the consent given")},React.createElement("span",null,(0,o.__)("UUID")," ",React.createElement(x.Z,{style:{color:"#9a9a9a"}}))),dataIndex:"uuid",key:"uuid",render:(e,t)=>React.createElement("code",null,t.uuid)},z())),React.createElement(j,(0,S.Z)({title:React.createElement(C.Z,{title:(0,o.__)("If you have allowed to log IP addresses, you can view them here. Otherwise you will see a salted hash of the IP address (truncated).")},React.createElement("span",null,(0,o.__)("IP")," ",React.createElement(x.Z,{style:{color:"#9a9a9a"}}))),dataIndex:"ip",key:"ip",render:(e,t)=>t.ipv4?React.createElement(React.Fragment,null,React.createElement("code",null,t.ipv4),!!y&&t.ipv4===y&&React.createElement(w.Z,{style:{marginLeft:5},color:"blue"},(0,o.__)("Exact match"))):t.ipv6?React.createElement(React.Fragment,null,React.createElement("code",null,t.ipv6),!!y&&t.ipv6===y&&React.createElement(w.Z,{style:{marginLeft:5},color:"blue"},(0,o.__)("Exact match"))):t.ipv4_hash?React.createElement(C.Z,{title:t.ipv4_hash},React.createElement("code",null,t.ipv4_hash.slice(0,8))):React.createElement(C.Z,{title:t.ipv6_hash},React.createElement("code",null,t.ipv6_hash.slice(0,8)))},z())),React.createElement(j,{title:(0,o.__)("Accepted services"),dataIndex:"decision",key:"decision",render:(e,t)=>t.decision_labels.map((e=>React.createElement(w.Z,{key:e},e)))})),React.createElement("p",{className:"description",style:{maxWidth:800,margin:"30px auto 0",textAlign:"center"}},B),React.createElement("p",{className:"description",style:{maxWidth:800,margin:"30px auto 0",textAlign:"center"}},H))}));var X=a(17603),J=a(24772),q=a(61497),Q=a(61811);const ee=(0,n.Pi)((()=>{const{message:e}=N.Z.useApp(),{optionStore:t}=(0,d.m)(),{navMenus:a,busyCountryBypassUpdate:n,others:{adminUrl:r}}=t,l=(0,s.useCallback)((async a=>{try{await t.addLinksToNavigationMenu(a),e.success((0,o.__)("Successfully added the links to your menu!"))}catch(t){e.error(t.responseJSON.message)}}),[]);return React.createElement(Q.Z,{loading:n,itemLayout:"horizontal",dataSource:a,size:"small",locale:{emptyText:React.createElement(g.Z,{description:(0,o.__)("No WordPress menu created yet.")},React.createElement("a",{href:`${r}nav-menus.php`,className:"button button-primary"},(0,o.__)("Create menu")))},renderItem:e=>{const{id:t,applied:a,edit_url:n,languages:r,name:c}=e;return React.createElement(Q.Z.Item,{actions:[a?React.createElement("a",{key:"apply"},React.createElement(q.Z,{style:{color:"#52c41a"}})," ",(0,o.__)("Already added")):React.createElement("a",{key:"applied",onClick:()=>l(t)},(0,o.__)("Add all legal links")),React.createElement("a",{key:"edit-manually",target:"_blank",href:n,rel:"noreferrer"},(0,o.__)("Edit manually"))]},React.createElement(Q.Z.Item.Meta,{title:React.createElement("span",null,c," ",r.length>0&&React.createElement(w.Z,null,r[0].language)),description:"legacy_nav"===e.type&&Object.values(e.locations).join(", ")}))}})}));var te=a(90987),ae=a(46499);const ne={labelCol:{span:24},wrapperCol:{span:24}},re=e=>{let{type:t}=e;const{message:a}=N.Z.useApp(),[n,r]=(0,s.useState)(""),l={tag:"a",id:"",text:"history"===t?(0,o._x)("Privacy settings history","legal-text"):"revoke"===t?(0,o._x)("Revoke consents","legal-text"):(0,o._x)("Change privacy settings","legal-text"),successmessage:"revoke"===t?(0,o._x)("You have successfully revoked consent for services with its cookies and personal data processing. The page will be reloaded now!","legal-text"):""},[c]=te.Z.useForm(),i=(0,s.useCallback)(((e,a)=>{const n=[];for(const e of Object.keys(a)){const t=a[e];t&&n.push(`${e}="${t.replace('"','\\"')}"`)}r(`[rcb-consent type="${t}" ${n.join(" ")}]`)}),[r]);(0,s.useEffect)((()=>{i(l,l)}),[]);const _=(0,s.useCallback)((()=>{(0,L.v)(n),a.success((0,o.__)("Successfully copied shortcode to clipboard!"))}),[n]);return React.createElement(te.Z,(0,S.Z)({name:`link-shortcode-${t}`,form:c},ne,{initialValues:l,onValuesChange:i}),React.createElement(te.Z.Item,{label:(0,o.__)("Appearance")},React.createElement(te.Z.Item,{name:"tag",noStyle:!0},React.createElement(ae.ZP.Group,null,React.createElement(ae.ZP.Button,{value:"a"},(0,o.__)("Link")),React.createElement(ae.ZP.Button,{value:"button"},(0,o.__)("Button")))),React.createElement("p",{className:"description"},(0,o.__)("It is recommended to use a simple link in your footer instead of a button to avoid visual misunderstandings."))),React.createElement(te.Z.Item,{label:(0,o.__)("HTML ID")},React.createElement(te.Z.Item,{name:"id",noStyle:!0},React.createElement($.Z,null)),React.createElement("p",{className:"description"},(0,o.__)("If you want to apply a custom style to the link/button, the shortcode output should be tagged with an ID attribute."))),React.createElement(te.Z.Item,{label:(0,o.__)("Text")},React.createElement(te.Z.Item,{name:"text",noStyle:!0},React.createElement($.Z,null)),React.createElement("p",{className:"description"},(0,o.__)("The user must be able to clearly understand the behaviour of the link/button from the name."))),"revoke"===t&&React.createElement(te.Z.Item,{label:(0,o.__)("Success message")},React.createElement(te.Z.Item,{name:"successmessage",noStyle:!0},React.createElement($.Z.TextArea,{autoSize:!0})),React.createElement("p",{className:"description"},(0,o.__)("After the consent is revoked, the page will be reloaded. Let the user know what happened with a success message."))),React.createElement(te.Z.Item,null,React.createElement(te.Z.Item,{noStyle:!0},React.createElement(E.Z,{style:{marginTop:0}},(0,o.__)("Result")),React.createElement($.Z.TextArea,{value:n,readOnly:!0,autoSize:!0})),React.createElement("p",{className:"description"},(0,o.__)("Copy the generated shortcode and paste it into your website.")),React.createElement("button",{className:"button alignright",onClick:_},(0,o.__)("Copy to clipboard"))))},{Panel:le}=X.Z,ce=(0,n.Pi)((()=>{const e=(0,M.v)("shortcodes");return React.createElement(React.Fragment,null,React.createElement(X.Z,{defaultActiveKey:["nav"],ghost:!0},React.createElement(le,{key:"nav",header:React.createElement("a",null,(0,o.__)("Add links to existing menu"))},React.createElement(J.Z,{style:{margin:5}},React.createElement(ee,null))),React.createElement(le,{key:"shortcode",header:React.createElement("a",null,(0,o.__)("Generate shortcode (advanced)"))},React.createElement(R.Z,null,React.createElement(h.Z,{xl:8,sm:12,xs:24},React.createElement(J.Z,{style:{margin:5},title:(0,o._x)("Change privacy settings","legal-text")},React.createElement(re,{type:"change"}))),React.createElement(h.Z,{xl:8,sm:12,xs:24},React.createElement(J.Z,{style:{margin:5},title:(0,o._x)("Privacy settings history","legal-text")},React.createElement(re,{type:"history"}))),React.createElement(h.Z,{xl:8,sm:12,xs:24},React.createElement(J.Z,{style:{margin:5},title:(0,o._x)("Revoke consents","legal-text")},React.createElement(re,{type:"revoke"})))))),React.createElement("span",{className:"description",style:{display:"block",maxWidth:800,margin:"30px auto 0",textAlign:"center"}},e))}));var oe=a(50088);const se=(0,n.Pi)((()=>{const e=(0,r.useParams)().tab||"",t=(0,r.useNavigate)();return React.createElement(c.Z,{defaultActiveKey:e,onChange:e=>{t(`/consent/${e}`)},items:[{key:"",label:(0,o.__)("Statistics"),children:React.createElement(l.f,{maxWidth:"fixed",style:{paddingTop:0}},React.createElement(f,null))},{key:"list",label:(0,o.__)("List of consents"),children:React.createElement(React.Fragment,null,React.createElement(z,null),React.createElement(oe.K,{identifier:"list-of-consents"}))},{key:"legal",label:(0,o.__)("Legal links"),children:React.createElement(React.Fragment,null,React.createElement(ce,null),React.createElement(oe.K,{identifier:"shortcodes"}))}]})}))},45567:(e,t,a)=>{a.d(t,{f:()=>n});const n=e=>{let{children:t,maxWidth:a="auto",style:n={}}=e;return React.createElement("div",{className:"rcb-config-content",style:{maxWidth:"fixed"===a?1300:a,...n}},t)}},73447:(e,t,a)=>{a.d(t,{D:()=>m});var n=a(46270),r=a(40045),l=a(48488),c=a.n(l),o=a(57350),s=a(53090);const i=o.Z.generatePicker(s.Z),{RangePicker:_}=i,m=e=>{const t=c().localeData();return React.createElement(_,(0,n.Z)({locale:{lang:{locale:c().locale(),placeholder:(0,r.__)("Select date"),rangePlaceholder:[(0,r.__)("Start date"),(0,r.__)("End date")],today:(0,r.__)("Today"),now:(0,r.__)("Now"),backToToday:(0,r.__)("Back to today"),ok:(0,r.__)("OK"),clear:(0,r.__)("Clear"),month:(0,r.__)("Month"),year:(0,r.__)("Year"),timeSelect:(0,r.__)("Select time"),dateSelect:(0,r.__)("Select date"),monthSelect:(0,r.__)("Choose a month"),yearSelect:(0,r.__)("Choose a year"),decadeSelect:(0,r.__)("Choose a decade"),yearFormat:"YYYY",dateFormat:t.longDateFormat("LL"),dayFormat:"D",dateTimeFormat:t.longDateFormat("LLL"),monthFormat:"MMMM",monthBeforeYear:!0,previousMonth:(0,r.__)("Previous month (PageUp)"),nextMonth:(0,r.__)("Next month (PageDown)"),previousYear:(0,r.__)("Last year (Control + left)"),nextYear:(0,r.__)("Next year (Control + right)"),previousDecade:(0,r.__)("Last decade"),nextDecade:(0,r.__)("Next decade"),previousCentury:(0,r.__)("Last century"),nextCentury:(0,r.__)("Next century")},timePickerLocale:{placeholder:(0,r.__)("Select time")},dateFormat:t.longDateFormat("LL"),dateTimeFormat:t.longDateFormat("LLL"),weekFormat:"YYYY-wo",monthFormat:"YYYY-MM"}},e))}}}]);
//# sourceMappingURL=https://sourcemap.devowl.io/real-cookie-banner/4.4.1/0a8beb5e545e5d7dc29cb5a72d4258a9/chunk-config-tab-consent.lite.js.map