@extends('layouts.codex')

@section('title', 'Privacy Policy — Azorsvault')
@section('description', 'Privacy policy of the Azorsvault MTG MCP server. Information about the collection and processing of personal data.')
@section('og_title', 'Privacy Policy — Azorsvault')
@section('og_description', 'Privacy policy of the Azorsvault MTG MCP server.')

@php
    $h2 = 'font-serif italic font-normal text-[28px] leading-tight text-parchment mt-12 mb-4';
    $h3 = 'font-serif italic font-normal text-[20px] leading-tight text-parchment mt-8 mb-3';
@endphp

@section('content')
    <article class="relative z-10 mx-auto max-w-[720px] px-7 py-20 sm:px-10 sm:py-24">
        <div class="text-center mb-12">
            <div class="font-mono text-[11px] tracking-[0.22em] uppercase text-accent mb-3.5">Legal</div>
            <h1 class="font-serif font-normal italic leading-tight tracking-tight text-[clamp(44px,6vw,64px)] m-0 text-parchment">Privacy Policy</h1>
        </div>

        <div class="space-y-4 text-[15px] leading-relaxed text-parchment/75">
            <section class="space-y-4">
                <h2 class="{{ $h2 }}">1. Privacy at a Glance</h2>
                <h3 class="{{ $h3 }}">General Information</h3>
                <p>The following information provides a simple overview of what happens to your personal data when you visit this website or use the Azorsvault MCP endpoint. Personal data is any data that can be used to personally identify you. For detailed information on the subject of data protection, please refer to the sections below.</p>

                <h3 class="{{ $h3 }}">Data Collection on This Website</h3>
                <p class="font-semibold text-parchment">Who is responsible for data collection on this website?</p>
                <p>Data processing on this website is carried out by the website operator. You can find the operator's contact details in the section "Notice Concerning the Responsible Party" below.</p>

                <p class="font-semibold text-parchment">How do we collect your data?</p>
                <p>Azorsvault has no forms, no user accounts, and no analytics. Data is collected only automatically by our IT systems when you visit the website or when Claude connects to the MCP endpoint — this is primarily technical data such as browser version, operating system, and time of request.</p>

                <p class="font-semibold text-parchment">What do we use your data for?</p>
                <p>Part of the data is collected to ensure error-free provision of the website and MCP server. Log data may be reviewed to diagnose errors or abuse.</p>

                <p class="font-semibold text-parchment">What rights do you have regarding your data?</p>
                <p>You have the right to receive information about the origin, recipient, and purpose of your stored personal data free of charge at any time. You also have the right to request the correction or deletion of this data. If you have given consent to data processing, you can revoke this consent at any time for the future. You also have the right to request the restriction of the processing of your personal data under certain circumstances. Furthermore, you have the right to lodge a complaint with the competent supervisory authority.</p>
                <p>You can contact us at any time regarding this and other questions on the subject of data protection.</p>
            </section>

            <section class="space-y-4">
                <h2 class="{{ $h2 }}">2. Hosting</h2>
                <h3 class="{{ $h3 }}">Hetzner</h3>
                <p>We host the contents of our website and MCP server with Hetzner Online GmbH, Industriestr. 25, 91710 Gunzenhausen, Germany (hereinafter "Hetzner").</p>
                <p>When you visit our website or call the MCP endpoint, Hetzner collects various log files including your IP addresses. For details, please refer to Hetzner's privacy policy: <a class="text-accent hover:text-parchment transition-colors" href="https://www.hetzner.com/legal/privacy-policy/" rel="nofollow noopener">https://www.hetzner.com/legal/privacy-policy/</a>.</p>
                <p>The use of Hetzner is based on Art. 6 (1) lit. f GDPR. We have a legitimate interest in the most reliable presentation of our service possible.</p>
            </section>

            <section class="space-y-4">
                <h2 class="{{ $h2 }}">3. General Information and Mandatory Disclosures</h2>

                <h3 class="{{ $h3 }}">Data Protection</h3>
                <p>The operator of this website takes the protection of your personal data very seriously. We treat your personal data confidentially and in accordance with the statutory data protection regulations and this privacy policy.</p>
                <p>When you use this website or the MCP endpoint, various personal data may be collected. This privacy policy explains what data we collect and what we use it for. It also explains how and for what purpose this is done.</p>
                <p>We would like to point out that data transmission over the Internet may have security vulnerabilities. Complete protection of data against access by third parties is not possible.</p>

                <h3 class="{{ $h3 }}">Notice Concerning the Responsible Party</h3>
                <p>The responsible party for data processing on this website is:</p>
                <p>Azorsvault</p>
                <p>Tobias Kokesch</p>
                <p>Gartenstraße 8</p>
                <p>90542 Eckental</p>
                <p>Germany</p>
                <p>Email: <a class="text-accent hover:text-parchment transition-colors" href="mailto:hello@binary-hype.com">hello@binary-hype.com</a></p>
                <p>The responsible party is the natural or legal person who, alone or jointly with others, decides on the purposes and means of processing personal data (e.g., names, email addresses, etc.).</p>

                <h3 class="{{ $h3 }}">Storage Duration</h3>
                <p>Unless a more specific storage period has been stated within this privacy policy, your personal data will remain with us until the purpose for data processing no longer applies. If you assert a legitimate request for deletion or revoke your consent to data processing, your data will be deleted unless we have other legally permissible reasons for storing your personal data; in the latter case, the deletion will take place after these reasons cease to apply.</p>

                <h3 class="{{ $h3 }}">Revocation of Your Consent to Data Processing</h3>
                <p>Many data processing operations are only possible with your express consent. You can revoke consent that has already been given at any time. The legality of the data processing carried out before the revocation remains unaffected.</p>

                <h3 class="{{ $h3 }}">Right to Data Portability</h3>
                <p>You have the right to have data that we process automatically on the basis of your consent or in fulfillment of a contract handed over to you or to a third party in a common, machine-readable format. If you request the direct transfer of data to another controller, this will only be done to the extent that it is technically feasible.</p>

                <h3 class="{{ $h3 }}">Information, Deletion, and Correction</h3>
                <p>Within the framework of the applicable legal provisions, you have the right to free information about your stored personal data, its origin and recipients, and the purpose of data processing and, if applicable, a right to correction or deletion of this data at any time. You can contact us at any time regarding this and other questions on the subject of personal data.</p>

                <h3 class="{{ $h3 }}">Right to Restriction of Processing</h3>
                <p>You have the right to request the restriction of the processing of your personal data. You can contact us at any time to exercise this right.</p>
            </section>

            <section class="space-y-4">
                <h2 class="{{ $h2 }}">4. Data Collection on This Website</h2>

                <h3 class="{{ $h3 }}">Server Log Files</h3>
                <p>The provider of the pages automatically collects and stores information in so-called server log files, which your browser or client automatically transmits to us. These are:</p>
                <ul class="list-disc pl-6 space-y-1.5 marker:text-accent/60">
                    <li>Browser or client type and version</li>
                    <li>Operating system used</li>
                    <li>Referrer URL</li>
                    <li>Hostname of the accessing computer</li>
                    <li>Time of the server request</li>
                    <li>IP address</li>
                </ul>
                <p>This data is not merged with other data sources.</p>
                <p>This data is collected on the basis of Art. 6 (1) lit. f GDPR. The website operator has a legitimate interest in the technically error-free presentation and optimization of the service — the server log files must be recorded for this purpose.</p>
            </section>

            <section class="space-y-4">
                <h2 class="{{ $h2 }}">5. MCP Endpoint and Third-Party Data Sources</h2>

                <h3 class="{{ $h3 }}">MCP Requests</h3>
                <p>When Claude (or another MCP client) connects to the Azorsvault endpoint at <code class="font-mono text-[0.9em] text-accent">/mcp/mtg</code>, the request contains the tool name being invoked (for example <code class="font-mono text-[0.9em] text-accent">search-cards-advanced</code>) and its arguments — typically card names, search filters, or rule numbers. These requests are processed in-memory to answer the query and are not stored beyond the standard server log files described above.</p>

                <h3 class="{{ $h3 }}">Scryfall</h3>
                <p>To answer card-related queries, Azorsvault fetches data from the Scryfall API operated by Scryfall LLC (<a class="text-accent hover:text-parchment transition-colors" href="https://scryfall.com" rel="nofollow noopener">https://scryfall.com</a>). These outbound calls include the search terms received from the MCP client but do not include the end-user's IP address or any identifying metadata. For details on Scryfall's processing, see <a class="text-accent hover:text-parchment transition-colors" href="https://scryfall.com/docs/privacy-policy" rel="nofollow noopener">https://scryfall.com/docs/privacy-policy</a>.</p>
                <p>The use of Scryfall is based on Art. 6 (1) lit. f GDPR — we have a legitimate interest in providing accurate, up-to-date Magic: The Gathering card data.</p>

                <h3 class="{{ $h3 }}">Comprehensive Rules</h3>
                <p>Rules queries are answered from a locally cached copy of the official Magic: The Gathering Comprehensive Rules as published by Wizards of the Coast. No outbound call is made per request; no personal data is transmitted.</p>
            </section>
        </div>
    </article>
@endsection
