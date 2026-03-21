@extends('layouts.app')
@section('title', 'Frequently Asked Questions – Hot Tub Buyer')
@section('content')

{{-- ══ HERO ══════════════════════════════════════════════════════════════════ --}}
<section class="svc-hero" style="border-bottom:1px solid var(--gray-200);">
    <div class="container" style="text-align:center;">
        <h1 class="svc-hero__title">Frequently Asked Questions</h1>
        <p class="svc-hero__desc" style="margin-bottom:0;">Everything you need to know about hot tubs and swim spas</p>
    </div>
</section>

{{-- ══ FAQ CONTENT ══════════════════════════════════════════════════════════ --}}
<section class="section section--white" style="padding-top:2.5rem;">
    <div class="container" style="max-width:760px;">

        {{-- ── Buying a Hot Tub ─────────────────────────────────────────── --}}
        <div class="faq-group">
            <h2 class="faq-group__title">Buying a Hot Tub</h2>
            <div class="faq-list">

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>How much does a hot tub cost?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Hot tub prices vary widely depending on size, features, and brand. Here's a general breakdown:</p>
                        <ul>
                            <li><strong>Entry-level / Inflatable:</strong> £400–£1,500 — basic jets, portable, lower insulation</li>
                            <li><strong>Mid-range:</strong> £3,000–£8,000 — solid acrylic shell, good jet count, decent insulation</li>
                            <li><strong>Premium:</strong> £8,000–£15,000 — superior insulation, more jets, advanced controls, brand warranty</li>
                            <li><strong>Luxury:</strong> £15,000–£30,000+ — top-tier brands like Jacuzzi or Hot Spring, full smart controls, premium materials</li>
                        </ul>
                        <p>Don't forget to budget for installation (£500–£2,000), ongoing running costs (£50–£100/month), and chemicals (£30–£60/month).</p>
                    </div>
                </div>

                <div class="faq-item faq-item--open">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>What size hot tub do I need?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer" style="display:block;">
                        <p>Hot tub capacity ranges from 2-person loungers to 8+ person family spas. Consider: 2–3 people = 3–4 seats, 4–5 people = 5–6 seats, 6+ people = 7–8 seats. Also consider your available space — measure your installation area and allow extra room for access and maintenance.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>What is the difference between hot tubs and swim spas?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p><strong>Hot tubs</strong> are compact (typically 6–9 ft), designed for relaxation and hydrotherapy. They feature multiple massage jets, seating for 2–8 people, and are heated to 37–40°C.</p>
                        <p><strong>Swim spas</strong> are much larger (12–20 ft), combining a swim current system with a hydrotherapy area. They allow you to swim in place against a continuous current, making them ideal for fitness as well as relaxation. They're more expensive but more versatile.</p>
                        <p>If your primary goal is relaxation and socialising, choose a hot tub. If you want to swim or exercise, a swim spa is the better choice.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>How long do hot tubs last?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>A well-maintained hot tub from a reputable brand will typically last <strong>15–20 years</strong>. Key factors affecting lifespan include:</p>
                        <ul>
                            <li><strong>Build quality:</strong> Premium brands use thicker acrylic shells and better insulation</li>
                            <li><strong>Maintenance:</strong> Regular water chemistry management and servicing significantly extends life</li>
                            <li><strong>Usage:</strong> Heavy daily use will wear components faster than moderate use</li>
                            <li><strong>Climate:</strong> Extreme cold or heat can stress components if not properly managed</li>
                        </ul>
                        <p>Budget models or inflatable hot tubs may only last 3–7 years. Annual professional servicing is the single best way to maximise longevity.</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Installation & Setup ─────────────────────────────────────── --}}
        <div class="faq-group">
            <h2 class="faq-group__title">Installation &amp; Setup</h2>
            <div class="faq-list">

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>Do I need planning permission for a hot tub?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>In most cases in the UK, <strong>you do not need planning permission</strong> to install a hot tub in your garden, as it falls under permitted development rights. However, there are exceptions:</p>
                        <ul>
                            <li>If you live in a listed building or conservation area</li>
                            <li>If the hot tub is to be installed on a raised platform above fence height</li>
                            <li>If it will be enclosed in a structure (e.g. a gazebo) that exceeds permitted development limits</li>
                            <li>If your property has had permitted development rights removed by a planning condition</li>
                        </ul>
                        <p>We always recommend checking with your local planning authority before installation if you're unsure.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>What electrical supply do I need?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Most hot tubs in the UK require a <strong>32-amp or 40-amp dedicated circuit</strong> supplied from your consumer unit (fuse board). This must be installed by a qualified electrician and comply with Part P of the Building Regulations.</p>
                        <p>Requirements typically include:</p>
                        <ul>
                            <li>Dedicated RCBO-protected circuit from consumer unit</li>
                            <li>IP65-rated weatherproof isolator switch within 3 metres of the hot tub</li>
                            <li>Earthing and bonding of all metalwork</li>
                            <li>Cable routed safely underground or in conduit</li>
                        </ul>
                        <p>Some smaller "plug and play" hot tubs run on a standard 13-amp socket but these have lower power and performance. Always consult your dealer and a registered electrician.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>What base do I need for a hot tub?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>A hot tub filled with water is extremely heavy — a 6-person hot tub can weigh 2,000–3,000 kg when full. Your base must be able to support this weight evenly. Suitable options include:</p>
                        <ul>
                            <li><strong>Reinforced concrete slab:</strong> The gold standard. At least 100mm thick with a steel mesh reinforcement. Perfectly level.</li>
                            <li><strong>Paving slabs:</strong> Suitable if properly laid on a solid compacted sub-base. Ensure no flex or movement.</li>
                            <li><strong>Decking:</strong> Only if specifically designed and engineered to support the weight. Standard garden decking is usually insufficient.</li>
                        </ul>
                        <p>The base must be completely level (within 5mm across the footprint) and have adequate drainage around it. Never place a hot tub on grass or loose soil.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>Can I install a hot tub myself?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>The physical positioning of a hot tub can be done yourself (with help — they're very heavy), but <strong>the electrical connection must be carried out by a qualified electrician</strong> to comply with UK law (Part P Building Regulations).</p>
                        <p>Self-installation steps you can do:</p>
                        <ul>
                            <li>Prepare the base (concrete slab etc.)</li>
                            <li>Move the hot tub into position using rollers or a crane/hiab service</li>
                            <li>Fill with water and add initial chemicals</li>
                            <li>Connect a pre-wired plug (for plug-and-play models)</li>
                        </ul>
                        <p>DIY installation may also void your manufacturer warranty — check the terms carefully. We recommend using our professional installation service for complete peace of mind.</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Running Costs & Maintenance ──────────────────────────────── --}}
        <div class="faq-group">
            <h2 class="faq-group__title">Running Costs &amp; Maintenance</h2>
            <div class="faq-list">

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>How much does it cost to run a hot tub?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Running costs depend on your model's energy efficiency, your local electricity rates, and how frequently you use it. Typical monthly costs in the UK:</p>
                        <ul>
                            <li><strong>Electricity:</strong> £40–£100/month (well-insulated models at the lower end)</li>
                            <li><strong>Chemicals:</strong> £20–£50/month</li>
                            <li><strong>Water:</strong> £5–£15/month (for quarterly drain and refill)</li>
                            <li><strong>Annual service:</strong> £100–£200/year</li>
                        </ul>
                        <p>Total: approximately <strong>£70–£160/month</strong>. Premium, well-insulated hot tubs (like those from Arctic Spas or Jacuzzi) can cost significantly less to run than cheaper models, often paying back the higher purchase price within a few years.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>How often should I change the water?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>As a general rule, you should completely drain and refill your hot tub every <strong>3–4 months</strong>. However, the exact frequency depends on how heavily it is used and how many bathers use it.</p>
                        <p>A useful formula: divide the hot tub volume in litres by the number of daily bathers, then divide by 12. For example, a 1,500-litre hot tub used by 3 people daily = 1,500 ÷ 3 ÷ 12 = change every 41 days.</p>
                        <p>Signs you need to change the water sooner: persistent cloudiness despite treatment, strong chemical smell, water that is difficult to balance, or foaming that won't clear.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>What chemicals do I need?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>The core chemicals every hot tub owner needs:</p>
                        <ul>
                            <li><strong>Sanitiser:</strong> Chlorine granules or bromine tablets/granules — keeps water bacteria-free</li>
                            <li><strong>pH Up / pH Down:</strong> Maintains pH between 7.2–7.6</li>
                            <li><strong>Total Alkalinity increaser:</strong> Stabilises pH, keeps it in range (target 80–120 ppm)</li>
                            <li><strong>Calcium Hardness increaser:</strong> Prevents corrosion and foaming (target 150–250 ppm)</li>
                            <li><strong>Shock treatment:</strong> Oxidises contaminants and restores water clarity (use weekly or after heavy use)</li>
                            <li><strong>Anti-foam:</strong> Quick fix for temporary foaming issues</li>
                            <li><strong>Filter cleaner:</strong> For monthly deep cleaning of filter cartridges</li>
                        </ul>
                        <p>Test your water 2–3 times per week using test strips or a liquid test kit. Starter kits are available from most hot tub dealers.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>How often do filters need replacing?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Filter cartridges should typically be <strong>replaced every 12 months</strong>, though this can vary depending on usage and care:</p>
                        <ul>
                            <li><strong>Rinse weekly</strong> with a hose to remove debris</li>
                            <li><strong>Deep clean monthly</strong> by soaking in filter cleaning solution overnight</li>
                            <li><strong>Replace annually</strong>, or sooner if the pleats are damaged, discoloured beyond cleaning, or the filter has been in use for longer than a year</li>
                        </ul>
                        <p>Rotating between two sets of filters (one soaking while one is in use) is the best practice for keeping your water consistently clean. Always ensure you use the correct filter model for your hot tub.</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Usage & Safety ───────────────────────────────────────────── --}}
        <div class="faq-group">
            <h2 class="faq-group__title">Usage &amp; Safety</h2>
            <div class="faq-list">

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>How long can I stay in a hot tub?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>For most healthy adults, <strong>15–30 minutes per session</strong> at 37–40°C is recommended. Extended soaking can cause dehydration, overheating, and dizziness.</p>
                        <p>Guidelines to stay safe:</p>
                        <ul>
                            <li>Keep the temperature at 37–38°C for longer sessions, 38–40°C for shorter ones</li>
                            <li>Drink plenty of water before and during your soak — avoid alcohol</li>
                            <li>Take breaks by sitting on the edge of the tub to cool down</li>
                            <li>Never use alone if you feel unwell, are very tired, or have consumed alcohol</li>
                            <li>Children should use lower temperatures (max 35°C) for shorter durations (max 15 minutes)</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>Can pregnant women use hot tubs?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p><strong>Pregnant women are generally advised to avoid hot tubs</strong>, particularly during the first trimester. Raising your core body temperature above 39°C (hyperthermia) during pregnancy has been linked to an increased risk of neural tube defects and other complications.</p>
                        <p>If you are pregnant and wish to use a hot tub:</p>
                        <ul>
                            <li>Always consult your midwife or GP first</li>
                            <li>Keep the temperature at or below 35°C</li>
                            <li>Limit sessions to no more than 10 minutes</li>
                            <li>Avoid hot tubs entirely during the first trimester</li>
                            <li>Exit immediately if you feel uncomfortable, dizzy, or overheated</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>Are hot tubs safe for children?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Hot tubs can be used by children but require extra precautions:</p>
                        <ul>
                            <li><strong>Age:</strong> Children under 5 should not use hot tubs. Children 5–12 should use lower temperatures (max 35°C) for no longer than 15 minutes</li>
                            <li><strong>Supervision:</strong> Children must always be supervised by an adult — never leave them unattended</li>
                            <li><strong>Temperature:</strong> Children overheat much faster than adults. Keep it cool and brief</li>
                            <li><strong>Jets:</strong> Ensure children keep hair tied back and away from suction fittings to prevent entrapment</li>
                            <li><strong>Cover:</strong> Always use a lockable safety cover when the tub is not in use to prevent unsupervised access</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>Can I use a hot tub in winter?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p><strong>Absolutely — many hot tub owners say winter is the best time to use them!</strong> Soaking in a hot tub on a cold evening is a wonderful experience. However, there are some important considerations:</p>
                        <ul>
                            <li><strong>Insulation:</strong> A well-insulated hot tub will maintain temperature efficiently even in freezing conditions. Poor insulation means much higher electricity bills in winter</li>
                            <li><strong>Freeze protection:</strong> Most quality hot tubs have built-in freeze protection that circulates water to prevent freezing. Never turn off your hot tub completely in freezing weather unless you fully drain and winterise it</li>
                            <li><strong>Cover condition:</strong> A good-quality, properly fitting cover is essential in winter to retain heat and reduce running costs</li>
                            <li><strong>Access:</strong> Ensure the path to your hot tub is safe and non-slip in icy conditions</li>
                        </ul>
                        <p>If you plan to leave your hot tub unused for an extended period in winter, consult our care guide for proper winterisation steps.</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Troubleshooting ──────────────────────────────────────────── --}}
        <div class="faq-group">
            <h2 class="faq-group__title">Troubleshooting</h2>
            <div class="faq-list">

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>Why is my hot tub water cloudy?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Cloudy water is one of the most common hot tub issues. The most likely causes and fixes:</p>
                        <ul>
                            <li><strong>Imbalanced chemistry:</strong> Test and adjust pH (7.2–7.6), Total Alkalinity (80–120 ppm), and Calcium Hardness (150–250 ppm)</li>
                            <li><strong>Low sanitiser:</strong> Shock treat with non-chlorine or chlorine shock, then maintain correct sanitiser levels</li>
                            <li><strong>Dirty or clogged filter:</strong> Remove and thoroughly rinse the filter. Deep clean or replace if necessary</li>
                            <li><strong>High bather load:</strong> Shock treat after heavy use. Consider a clarifier to coagulate fine particles</li>
                            <li><strong>Old water:</strong> If the water hasn't been changed in more than 3–4 months, a full drain and refill is the best solution</li>
                        </ul>
                        <p>If cloudy water persists despite treatment, contact our service team for a professional water test.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>Why won't my hot tub heat up?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>If your hot tub is not reaching temperature or is heating very slowly, check the following:</p>
                        <ul>
                            <li><strong>Cover condition:</strong> A damaged or waterlogged cover loses enormous amounts of heat. Inspect for tears, sagging, or excessive weight</li>
                            <li><strong>Filter blockage:</strong> A clogged filter restricts water flow through the heater. Clean or replace filters</li>
                            <li><strong>Flow error (FLO):</strong> Check for error codes on your topside panel. A flow error means insufficient water is reaching the heater</li>
                            <li><strong>Heater element failure:</strong> The element may have burnt out and require replacement by a technician</li>
                            <li><strong>Air lock:</strong> After refilling, an air lock in the pumps can prevent water circulation. Run jets on high to purge air</li>
                            <li><strong>Thermostat setting:</strong> Verify the temperature is set correctly on the control panel</li>
                        </ul>
                        <p>If none of the above resolve the issue, <a href="/services" style="color:var(--teal);font-weight:600;">request a service visit</a> from one of our certified technicians.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>What causes foam in my hot tub?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Foam is caused by surfactants — substances that lower water surface tension. Common sources:</p>
                        <ul>
                            <li><strong>Body products:</strong> Cosmetics, lotions, hair products, and natural body oils — always shower before entering</li>
                            <li><strong>Detergent residue:</strong> Swimwear washed in laundry detergent — rinse swimwear in clean water before use</li>
                            <li><strong>Low calcium hardness:</strong> Soft water foams easily — increase Calcium Hardness to 150–250 ppm</li>
                            <li><strong>Old, contaminated water:</strong> TDS (Total Dissolved Solids) builds up over time — drain and refill</li>
                        </ul>
                        <p>For a quick fix, use an anti-foam product. For a permanent solution, identify and eliminate the source, shock treat the water, and consider a full water change if the problem persists.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-btn" onclick="toggleFaq(this)">
                        <span>My hot tub has an error code — what do I do?</span>
                        <svg class="faq-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="faq-answer">
                        <p>Error codes vary by brand and model. Here are the most common codes and what they mean:</p>
                        <ul>
                            <li><strong>FLO / FL1 / FL2:</strong> Flow error — insufficient water flow through heater. Check filters, water level, and circulation pump</li>
                            <li><strong>OH / OHH:</strong> Overheat — water temperature too high. Turn off, remove cover, and let cool. Check thermostat and sensors</li>
                            <li><strong>DR / DRY:</strong> Dry fire protection — heater fired without water. Check for air locks, low water level, or blocked filters</li>
                            <li><strong>ICE / FRZ:</strong> Freeze protection activated — ambient temperature very low. Ensure freeze protection mode is functioning</li>
                            <li><strong>Sn / SnX:</strong> Sensor error — temperature sensor may be faulty. May require technician to replace sensor</li>
                        </ul>
                        <p>Always consult your hot tub's manual for model-specific codes. If you cannot resolve the error, <a href="/services" style="color:var(--teal);font-weight:600;">book an emergency service visit</a> — do not continue using the hot tub if displaying a heater or sensor error.</p>
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Still Have Questions CTA ─────────────────────────────────── --}}
        <div class="faq-cta">
            <h3 class="faq-cta__title">Still have questions?</h3>
            <p class="faq-cta__desc">Get in touch with our expert team or request quotes from approved dealers</p>
            <a href="/hot-tubs" class="btn btn--ghost btn--pill">Browse Hot Tubs</a>
        </div>

    </div>
</section>

<script>
function toggleFaq(btn) {
    const item   = btn.closest('.faq-item');
    const answer = item.querySelector('.faq-answer');
    const isOpen = item.classList.contains('faq-item--open');

    // Close all open items in the same group
    btn.closest('.faq-list').querySelectorAll('.faq-item--open').forEach(openItem => {
        openItem.classList.remove('faq-item--open');
        openItem.querySelector('.faq-answer').style.display = 'none';
    });

    // Toggle clicked item
    if (!isOpen) {
        item.classList.add('faq-item--open');
        answer.style.display = 'block';
    }
}
</script>

@endsection