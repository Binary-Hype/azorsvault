@extends('layouts.codex')

@section('title', 'Privacy Policy — Azorsvault')
@section('description', 'Privacy policy of the Azorsvault MTG MCP server. Information about the collection and processing of personal data.')
@section('og_title', 'Privacy Policy — Azorsvault')
@section('og_description', 'Privacy policy of the Azorsvault MTG MCP server.')

@section('content')
    <x-legal.page heading="Privacy Policy">
        <x-legal.section heading="1. Privacy at a Glance">
            <x-legal.subheading>General Information</x-legal.subheading>
            <p>The following information provides a simple overview of what happens to your personal data when you visit this website or use the Azorsvault MCP endpoint. Personal data is any data that can be used to personally identify you. For detailed information on the subject of data protection, please refer to the sections below.</p>

            <x-legal.subheading>Data Collection on This Website</x-legal.subheading>
            <p class="font-semibold text-parchment">Who is responsible for data collection on this website?</p>
            <p>Data processing on this website is carried out by the website operator. You can find the operator's contact details in the section "Notice Concerning the Responsible Party" below.</p>

            <p class="font-semibold text-parchment">How do we collect your data?</p>
            <p>Azorsvault has no forms and no user accounts. Data is collected automatically by our IT systems when you visit the website or when Claude connects to the MCP endpoint — this is primarily technical data such as browser version, operating system, and time of request. In addition, we measure reach with a self-hosted analytics instance that works without cookies and without any identifier on your device; this is described under "Reach Measurement" below.</p>

            <p class="font-semibold text-parchment">What do we use your data for?</p>
            <p>Part of the data is collected to ensure error-free provision of the website and MCP server. Log data may be reviewed to diagnose errors or abuse. Reach measurement tells us in aggregate which pages are used, so we can improve the service.</p>

            <p class="font-semibold text-parchment">What rights do you have regarding your data?</p>
            <p>You have the right to receive information about the origin, recipient, and purpose of your stored personal data free of charge at any time. You also have the right to request the correction or deletion of this data. If you have given consent to data processing, you can revoke this consent at any time for the future. You also have the right to request the restriction of the processing of your personal data under certain circumstances. Furthermore, you have the right to lodge a complaint with the competent supervisory authority.</p>
            <p>You can contact us at any time regarding this and other questions on the subject of data protection.</p>
        </x-legal.section>

        <x-legal.section heading="2. Hosting">
            <x-legal.subheading>Hetzner</x-legal.subheading>
            <p>We host the contents of our website and MCP server with Hetzner Online GmbH, Industriestr. 25, 91710 Gunzenhausen, Germany (hereinafter "Hetzner").</p>
            <p>When you visit our website or call the MCP endpoint, Hetzner collects various log files including your IP addresses. For details, please refer to Hetzner's privacy policy: <x-legal.link href="https://www.hetzner.com/legal/privacy-policy/">https://www.hetzner.com/legal/privacy-policy/</x-legal.link>.</p>
            <p>The use of Hetzner is based on Art. 6 (1) lit. f GDPR. We have a legitimate interest in the most reliable presentation of our service possible.</p>
        </x-legal.section>

        <x-legal.section heading="3. General Information and Mandatory Disclosures">
            <x-legal.subheading>Data Protection</x-legal.subheading>
            <p>The operator of this website takes the protection of your personal data very seriously. We treat your personal data confidentially and in accordance with the statutory data protection regulations and this privacy policy.</p>
            <p>When you use this website or the MCP endpoint, various personal data may be collected. This privacy policy explains what data we collect and what we use it for. It also explains how and for what purpose this is done.</p>
            <p>We would like to point out that data transmission over the Internet may have security vulnerabilities. Complete protection of data against access by third parties is not possible.</p>

            <x-legal.subheading>Notice Concerning the Responsible Party</x-legal.subheading>
            <p>The responsible party for data processing on this website is:</p>
            <div class="space-y-1.5">
                <p>Azorsvault</p>
                <p>Tobias Kokesch</p>
                <p>Gartenstraße 8</p>
                <p>90542 Eckental</p>
                <p>Germany</p>
            </div>
            <p>Email: <x-legal.link href="mailto:hello@binary-hype.com">hello@binary-hype.com</x-legal.link></p>
            <p>The responsible party is the natural or legal person who, alone or jointly with others, decides on the purposes and means of processing personal data (e.g., names, email addresses, etc.).</p>

            <x-legal.subheading>Storage Duration</x-legal.subheading>
            <p>Unless a more specific storage period has been stated within this privacy policy, your personal data will remain with us until the purpose for data processing no longer applies. If you assert a legitimate request for deletion or revoke your consent to data processing, your data will be deleted unless we have other legally permissible reasons for storing your personal data; in the latter case, the deletion will take place after these reasons cease to apply.</p>

            <x-legal.subheading>Revocation of Your Consent to Data Processing</x-legal.subheading>
            <p>Many data processing operations are only possible with your express consent. You can revoke consent that has already been given at any time. The legality of the data processing carried out before the revocation remains unaffected.</p>

            <x-legal.subheading>Right to Data Portability</x-legal.subheading>
            <p>You have the right to have data that we process automatically on the basis of your consent or in fulfillment of a contract handed over to you or to a third party in a common, machine-readable format. If you request the direct transfer of data to another controller, this will only be done to the extent that it is technically feasible.</p>

            <x-legal.subheading>Information, Deletion, and Correction</x-legal.subheading>
            <p>Within the framework of the applicable legal provisions, you have the right to free information about your stored personal data, its origin and recipients, and the purpose of data processing and, if applicable, a right to correction or deletion of this data at any time. You can contact us at any time regarding this and other questions on the subject of personal data.</p>

            <x-legal.subheading>Right to Restriction of Processing</x-legal.subheading>
            <p>You have the right to request the restriction of the processing of your personal data. You can contact us at any time to exercise this right.</p>
        </x-legal.section>

        <x-legal.section heading="4. Data Collection on This Website">
            <x-legal.subheading>Server Log Files</x-legal.subheading>
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

            <x-legal.subheading>Reach Measurement (Umami)</x-legal.subheading>
            <p>This website uses Umami for reach measurement. Umami runs on our own server at <code class="font-mono text-[0.9em] text-accent">analytics.notonfire.systems</code>, operated by the same responsible party named above as part of the NotOnFire infrastructure and hosted with Hetzner in Germany. No data is transmitted to third parties, and no data leaves the European Union.</p>
            <p>Umami works <strong class="font-semibold text-parchment">without cookies</strong> and places no identifier on your device. To group the visits of a single day, a hash that rotates daily is derived server-side from your IP address, user agent, and the domain; the IP address itself is not stored. There is no recognition across devices, across sites, or across days.</p>
            <p>The following is recorded:</p>
            <ul class="list-disc pl-6 space-y-1.5 marker:text-accent/60">
                <li>The page visited and the referrer</li>
                <li>An approximate country derived from the IP address</li>
                <li>Device type, browser, and operating system</li>
                <li>Page-timing measurements (how quickly the page rendered)</li>
            </ul>
            <p>Search parameters and URL fragments are discarded before storage, and the count honours your browser's <code class="font-mono text-[0.9em] text-accent">Do Not Track</code> setting — if you enable it, no measurement takes place. Measurement data is kept for up to twelve months.</p>
            <p>The legal basis is Art. 6 (1) lit. f GDPR. We have a legitimate interest in the statistical, non-personalised analysis of usage in order to improve our service. Because the measurement uses no cookies and stores no information on your device, it requires no consent under § 25 (1) TTDSG.</p>
        </x-legal.section>

        <x-legal.section heading="5. MCP Endpoint and Third-Party Data Sources">
            <x-legal.subheading>MCP Requests</x-legal.subheading>
            <p>When Claude (or another MCP client) connects to the Azorsvault endpoint at <code class="font-mono text-[0.9em] text-accent">/mcp/mtg</code>, the request contains the tool name being invoked (for example <code class="font-mono text-[0.9em] text-accent">search-cards-advanced</code>) and its arguments — typically card names, search filters, or rule numbers. These requests are processed in-memory to answer the query and are not stored beyond the standard server log files described above. The MCP endpoint loads no measurement script and is not counted by Umami.</p>

            <x-legal.subheading>Scryfall</x-legal.subheading>
            <p>To answer card-related queries, Azorsvault fetches data from the Scryfall API operated by Scryfall LLC (<x-legal.link href="https://scryfall.com">https://scryfall.com</x-legal.link>). These outbound calls include the search terms received from the MCP client but do not include the end-user's IP address or any identifying metadata. For details on Scryfall's processing, see <x-legal.link href="https://scryfall.com/docs/privacy-policy">https://scryfall.com/docs/privacy-policy</x-legal.link>.</p>
            <p>The use of Scryfall is based on Art. 6 (1) lit. f GDPR — we have a legitimate interest in providing accurate, up-to-date Magic: The Gathering card data.</p>

            <x-legal.subheading>Comprehensive Rules</x-legal.subheading>
            <p>Rules queries are answered from a locally cached copy of the official Magic: The Gathering Comprehensive Rules as published by Wizards of the Coast. No outbound call is made per request; no personal data is transmitted.</p>
        </x-legal.section>
    </x-legal.page>
@endsection
