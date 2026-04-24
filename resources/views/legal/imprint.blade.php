@extends('layouts.codex')

@section('title', 'Imprint — Azorsvault')
@section('description', 'Legal notice and provider identification for the Azorsvault MTG MCP server.')
@section('og_title', 'Imprint — Azorsvault')
@section('og_description', 'Legal notice and provider identification for the Azorsvault MTG MCP server.')

@section('content')
    <article class="relative z-10 mx-auto max-w-[720px] px-7 py-20 sm:px-10 sm:py-24">
        <div class="text-center mb-12">
            <div class="font-mono text-[11px] tracking-[0.22em] uppercase text-accent mb-3.5">Legal</div>
            <h1 class="font-serif font-normal italic leading-tight tracking-tight text-[clamp(44px,6vw,64px)] m-0 text-parchment">Imprint</h1>
        </div>

        <div class="space-y-6 text-[15px] leading-relaxed text-parchment/75">
            <section class="space-y-3">
                <h2 class="font-serif italic font-normal text-[28px] leading-tight text-parchment mt-10 mb-4">Information according to § 5 TMG</h2>
                <p>Azorsvault</p>
                <p>Tobias Kokesch</p>
                <p>Gartenstraße 8</p>
                <p>90542 Eckental</p>
                <p>Germany</p>
            </section>

            <section class="space-y-3">
                <h2 class="font-serif italic font-normal text-[28px] leading-tight text-parchment mt-10 mb-4">Contact</h2>
                <p>Email: <a class="text-accent hover:text-parchment transition-colors" href="mailto:hello@binary-hype.com">hello@binary-hype.com</a></p>
            </section>

            <section class="space-y-3">
                <h2 class="font-serif italic font-normal text-[28px] leading-tight text-parchment mt-10 mb-4">Responsible for content according to § 55 (2) RStV</h2>
                <p>Tobias Kokesch</p>
                <p>Gartenstraße 8</p>
                <p>90542 Eckental</p>
            </section>

            <section class="space-y-4">
                <h2 class="font-serif italic font-normal text-[28px] leading-tight text-parchment mt-10 mb-4">Dispute Resolution</h2>
                <p>The European Commission provides a platform for online dispute resolution (ODR): <a class="text-accent hover:text-parchment transition-colors" href="https://ec.europa.eu/consumers/odr/" rel="nofollow noopener">https://ec.europa.eu/consumers/odr/</a>. You can find our email address in the imprint above.</p>
                <p>We are neither willing nor obliged to participate in dispute resolution proceedings before a consumer arbitration board.</p>
            </section>

            <section class="space-y-4">
                <h2 class="font-serif italic font-normal text-[28px] leading-tight text-parchment mt-10 mb-4">Liability for Content</h2>
                <p>As a service provider, we are responsible for our own content on these pages in accordance with general legislation pursuant to § 7 (1) TMG. However, according to §§ 8 to 10 TMG, we are not obligated to monitor transmitted or stored third-party information or to investigate circumstances that indicate illegal activity.</p>
                <p>Obligations to remove or block the use of information under general law remain unaffected. However, liability in this regard is only possible from the point in time at which a concrete infringement of the law becomes known. If we become aware of any such infringements, we will remove the relevant content immediately.</p>
            </section>

            <section class="space-y-4">
                <h2 class="font-serif italic font-normal text-[28px] leading-tight text-parchment mt-10 mb-4">Liability for Links</h2>
                <p>Our website contains links to external third-party websites over whose content we have no influence. Therefore, we cannot assume any liability for this external content. The respective provider or operator of the pages is always responsible for the content of the linked pages. The linked pages were checked for possible legal violations at the time of linking. Illegal content was not recognizable at the time of linking.</p>
                <p>However, permanent monitoring of the content of linked pages is not reasonable without concrete evidence of a violation. If we become aware of any legal violations, we will remove such links immediately.</p>
            </section>

            <section class="space-y-4">
                <h2 class="font-serif italic font-normal text-[28px] leading-tight text-parchment mt-10 mb-4">Copyright</h2>
                <p>The content and works created by the site operators on these pages are subject to German copyright law. Duplication, processing, distribution, and any form of commercialization beyond the scope of copyright law require the written consent of the respective author or creator. Downloads and copies of this site are only permitted for private, non-commercial use.</p>
                <p>Insofar as the content on this site was not created by the operator, the copyrights of third parties are respected. In particular, third-party content is identified as such. Should you nevertheless become aware of a copyright infringement, please inform us accordingly. If we become aware of any infringements, we will remove such content immediately.</p>
                <p>Magic: The Gathering is a trademark of Wizards of the Coast LLC. Azorsvault is not affiliated with, endorsed, sponsored, or specifically approved by Wizards of the Coast. Card data is provided via the Scryfall API under Scryfall's terms of use.</p>
            </section>
        </div>
    </article>
@endsection
