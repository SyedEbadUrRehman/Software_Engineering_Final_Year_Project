<script setup>
import { ref, onMounted } from 'vue'
import Header from "../Components/Index/Header.vue"
import Footer from "../Components/Index/Footer.vue"
import { Head } from "@inertiajs/vue3";
const showConsentBanner = ref(true)
const showCustomize = ref(false)
const openCategories = ref([])

const userRights = ref([
  { title: 'Right to Access', desc: 'You can request a copy of the personal data we hold about you.' },
  { title: 'Right to Rectification', desc: 'You can request correction of inaccurate or incomplete data.' },
  { title: 'Right to Erasure', desc: 'You can request deletion of your data in certain circumstances.' },
  { title: 'Right to Restriction', desc: 'You can request we limit how we use your data.' },
  { title: 'Right to Data Portability', desc: 'You can request your data in a structured, machine-readable format.' },
  { title: 'Right to Object', desc: 'You can object to our processing based on legitimate interests or for marketing purposes.' },
  { title: 'Right to Withdraw Consent', desc: 'You can withdraw consent at any time where we rely on consent as the legal basis.' },
  { title: 'Right to Lodge a Complaint', desc: 'You have the right to complain to a supervisory authority.' }
])

const cookieCategories = ref([
  {
    id: 'necessary',
    name: 'Necessary',
    count: 5,
    enabled: true,
    description: 'Necessary cookies help make our site usable by enabling basic functions like page navigation and access to secure areas of the site. The site cannot function properly without these cookies.',
    cookies: [
      { id: 'cf-turnstile', name: 'cf.turnstile.u', provider: 'Cloudflare', purpose: 'This cookie is used to distinguish between humans and bots.', duration: 'Persistent', type: 'HTML Local Storage' },
      { id: 'cookiebot', name: 'CookieConsent', provider: 'Cookiebot', purpose: 'Stores the user\'s cookie consent state for the current domain.', duration: '1 year', type: 'HTTP Cookie' },
      { id: 'google-test', name: 'test_cookie', provider: 'Google', purpose: 'Used to check if the user\'s browser supports cookies.', duration: '1 day', type: 'HTTP Cookie' },
      { id: 'hcaptcha-cf', name: '__cf_bm', provider: 'hcaptcha.com', purpose: 'Distinguishes between humans and bots for valid website use reports.', duration: '1 day', type: 'HTTP Cookie' },
      { id: 'chargebee-auth', name: 'authType', provider: 'Chargebee', purpose: 'Authentication type for payment processing.', duration: 'Persistent', type: 'HTML Local Storage' }
    ]
  },
  {
    id: 'preferences',
    name: 'Preferences',
    count: 0,
    enabled: false,
    description: 'Preference cookies enable our site to remember information that changes the way the website behaves or looks, like your preferred language or the region that you are in.',
    cookies: [
      { id: 'none', name: 'No cookies currently in use', provider: 'N/A', purpose: 'We do not use cookies of this type.', duration: 'N/A', type: 'N/A' }
    ]
  },
  {
    id: 'statistics',
    name: 'Statistics',
    count: 5,
    enabled: false,
    description: 'Statistic cookies help us understand how visitors interact with our site by collecting and reporting information - this is done anonymously.',
    cookies: [
      { id: 'hj-viewport', name: 'hjActiveViewportIds', provider: 'Hotjar', purpose: 'Contains non-personal information on what subpages the visitor enters to optimize experience.', duration: 'Persistent', type: 'HTML Local Storage' },
      { id: 'hj-screen', name: 'hjViewportId', provider: 'Hotjar', purpose: 'Saves the user\'s screen size in order to adjust the size of images on the website.', duration: 'Session', type: 'HTML Local Storage' },
      { id: 'hj-session', name: '_hjSession_#', provider: 'Hotjar', purpose: 'Collects statistics on visits, average time spent, and pages read.', duration: '1 day', type: 'HTTP Cookie' },
      { id: 'hj-session-user', name: '_hjSessionUser_#', provider: 'Hotjar', purpose: 'Collects long-term statistics on the visitor\'s visits to the website.', duration: '1 year', type: 'HTTP Cookie' },
      { id: 'hj-tld', name: '_hjTLDTest', provider: 'Hotjar', purpose: 'Registers statistical data on users\' behaviour for internal analytics.', duration: 'Session', type: 'HTTP Cookie' }
    ]
  },
  {
    id: 'marketing',
    name: 'Marketing',
    count: 6,
    enabled: false,
    description: 'Our site has Google Analytics, but we don\'t use the cookies generated for it for marketing purposes.',
    cookies: [
      { id: 'google-nid', name: 'NID', provider: 'Google', purpose: 'Registers a unique ID that identifies a returning user\'s device for targeted ads.', duration: '6 months', type: 'HTTP Cookie' },
      { id: 'google-ads', name: 'pagead/1p-user-list/#', provider: 'Google', purpose: 'Tracks interest in specific products/events across sites for ad measurement.', duration: 'Session', type: 'Pixel Tracker' },
      { id: 'google-ga', name: '_ga', provider: 'Google', purpose: 'Sends data to Google Analytics about the visitor\'s device and behavior.', duration: '2 years', type: 'HTTP Cookie' },
      { id: 'google-ga-id', name: '_ga_#', provider: 'Google', purpose: 'Sends data to Google Analytics about the visitor\'s device and behavior.', duration: '2 years', type: 'HTTP Cookie' },
      { id: 'google-gcl-au', name: '_gcl_au', provider: 'Google', purpose: 'Used by Google AdSense for experimenting with advertisement efficiency.', duration: '3 months', type: 'HTTP Cookie' },
      { id: 'google-gcl-ls', name: '_gcl_ls', provider: 'Google', purpose: 'Tracks conversion rate between user and ad banners to optimize relevance.', duration: 'Persistent', type: 'HTML Local Storage' }
    ]
  }
])

const acceptAllCookies = () => {
  cookieCategories.value.forEach(category => { category.enabled = true })
  showConsentBanner.value = false
  savePreferences()
}

const rejectAllCookies = () => {
  cookieCategories.value.forEach(category => {
    if (category.id !== 'necessary') category.enabled = false
  })
  showConsentBanner.value = false
  savePreferences()
}

const toggleCategory = (categoryId) => {
  const index = openCategories.value.indexOf(categoryId)
  if (index > -1) {
    openCategories.value.splice(index, 1)
  } else {
    openCategories.value.push(categoryId)
  }
}

const isCategoryOpen = (categoryId) => {
  return openCategories.value.includes(categoryId)
}

const togglePreference = (categoryId) => {
  const category = cookieCategories.value.find(cat => cat.id === categoryId)
  if (category && categoryId !== 'necessary') {
    category.enabled = !category.enabled
  }
}

const savePreferences = () => {
  const preferences = {}
  cookieCategories.value.forEach(category => {
    preferences[category.id] = category.enabled
  })
  localStorage.setItem('siteclip-cookie-preferences', JSON.stringify(preferences))
  showCustomize.value = false
}

const loadPreferences = () => {
  const savedPreferences = localStorage.getItem('siteclip-cookie-preferences')
  if (savedPreferences) {
    const preferences = JSON.parse(savedPreferences)
    cookieCategories.value.forEach(category => {
      if (preferences[category.id] !== undefined) {
        category.enabled = preferences[category.id]
      }
    })
    showConsentBanner.value = false
  }
}

onMounted(() => {
  loadPreferences()
  if (navigator.globalPrivacyControl) {
    rejectAllCookies()
  }
})
</script>

<template>
     <Head title="Privacy | Policy" />
    <Header/>
  <div class="privacy-policy-container">
    <!-- Cookie Consent Banner -->
    <div v-if="showConsentBanner" class="cookie-banner">
      <div class="cookie-content">
        <h3>🍪 We value your privacy</h3>
        <p>
          We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. 
          By clicking "Accept All", you consent to our use of cookies. 
          <a href="#read-more" class="link">Read more</a> about our cookie practices.
        </p>
        <div class="cookie-actions">
          <button @click="acceptAllCookies" class="btn btn-primary">Accept All</button>
          <button @click="showCustomize = true" class="btn btn-secondary">Customize</button>
          <button @click="rejectAllCookies" class="btn btn-outline">Reject All</button>
        </div>
      </div>
    </div>

    <!-- Main Privacy Policy Content -->
    <div class="privacy-content">
      <h1>Privacy Policy for SiteClip</h1>
      <p class="last-updated">Last updated: August 5, 2026</p>
      
      <section id="introduction">
        <h2>1. Introduction</h2>
        <p>
          This privacy policy ("Policy") describes how SiteClip ("we," "us," or "our") collects, uses, 
          and protects your personal data when you use our website [siteclip.com] and related services 
          (collectively, the "Service"). This Policy complies with:
        </p>
        <ul>
          <li><strong>Pakistan's Prevention of Electronic Crimes Act 2016 (PECA)</strong></li>
          <li><strong>Pakistan's Personal Data Protection Bill 2023 (PDPB)</strong> (once enacted)</li>
          <li><strong>EU General Data Protection Regulation (GDPR)</strong> for relevant data subjects</li>
        </ul>
        <p>
          We take your privacy seriously and are committed to protecting your personal data. 
          This Policy should be read alongside our <router-link to="/terms" class="link">Terms of Service</router-link>.
        </p>
      </section>

      <section id="data-controller">
        <h2>2. Data Controller</h2>
        <p>The data controller responsible for your personal data is:</p>
        <div class="data-controller-details">
          <p><strong>SiteClip Ltd.</strong><br>
          [Your Physical Address]<br>
          Pakistan<br>
          Email: privacy@siteclip.com<br>
          Phone: +92-XXX-XXXXXXX
          </p>
        </div>
      </section>

      <section id="data-collection">
        <h2>3. Information We Collect</h2>
        <div class="data-table">
          <h3>Personal Data</h3>
          <p>We may collect the following personal data:</p>
          <table>
            <thead>
              <tr>
                <th>Data Type</th>
                <th>Purpose</th>
                <th>Legal Basis (GDPR)</th>
                <th>Legal Basis (Pakistan)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Account information (name, email, password)</td>
                <td>Service provision, account management</td>
                <td>Contract performance</td>
                <td>Consent (PDPB) / Legitimate interest (PECA)</td>
              </tr>
              <tr>
                <td>Usage data (pages visited, features used)</td>
                <td>Analytics, service improvement</td>
                <td>Legitimate interests</td>
                <td>Legitimate interest (PECA)</td>
              </tr>
              <tr>
                <td>Device information (browser type, IP address)</td>
                <td>Security, analytics</td>
                <td>Legitimate interests</td>
                <td>Security measures (PECA)</td>
              </tr>
              <tr>
                <td>Communication data (support tickets, feedback)</td>
                <td>Customer support, service improvement</td>
                <td>Contract performance, legitimate interests</td>
                <td>Consent (PDPB)</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section id="cookies-section">
        <h2>4. Cookies and Tracking Technologies</h2>
        <p>
          Our website uses cookies and similar tracking technologies to enhance your experience. 
          Cookies are small text files stored on your device when you visit our website. 
          We use cookies for:
        </p>
        <ul>
          <li><strong>Essential functionality:</strong> Remembering your preferences and login status</li>
          <li><strong>Analytics:</strong> Understanding how you use our website (Hotjar, Google Analytics)</li>
          <li><strong>Performance:</strong> Optimizing website speed and functionality (Cloudflare)</li>
          <li><strong>Marketing:</strong> Delivering relevant advertisements (Google Ads)</li>
        </ul>
        
        <div class="cookie-categories">
          <h3>Cookie Categories</h3>
          <div v-for="category in cookieCategories" :key="category.id" class="cookie-category">
            <div class="category-header" @click="toggleCategory(category.id)">
              <h4>{{ category.name }} ({{ category.count }})</h4>
              <span class="toggle-icon">{{ isCategoryOpen(category.id) ? '▼' : '▶' }}</span>
            </div>
            <div v-if="isCategoryOpen(category.id)" class="category-details">
              <p>{{ category.description }}</p>
              <div v-for="cookie in category.cookies" :key="cookie.id" class="cookie-item">
                <h5>{{ cookie.name }}</h5>
                <p><strong>Provider:</strong> {{ cookie.provider }}</p>
                <p><strong>Purpose:</strong> {{ cookie.purpose }}</p>
                <p><strong>Duration:</strong> {{ cookie.duration }}</p>
                <p><strong>Type:</strong> {{ cookie.type }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="consent-management">
          <h3>5. Your Cookie Consent Choices</h3>
          <p>
            Under the GDPR and Pakistan's emerging data protection framework, you have the right to 
            control how your data is used. You can:
          </p>
          <ul>
            <li><strong>Accept all cookies:</strong> Enable all cookie categories for optimal experience</li>
            <li><strong>Customize settings:</strong> Select specific cookie categories you wish to allow</li>
            <li><strong>Reject all:</strong> Only enable essential cookies necessary for site functionality</li>
          </ul>
          <p>
            You can change your cookie preferences at any time by clicking on the cookie settings icon 
            or visiting this privacy policy page. Please note that disabling certain cookies may affect 
            your experience on our website.
          </p>
        </div>
      </section>

      <section id="legal-basis">
        <h2>6. Legal Bases for Processing</h2>
        <p>We process your personal data based on the following legal grounds:</p>
        <div class="legal-bases-grid">
          <div class="legal-basis">
            <h3>For GDPR Compliance:</h3>
            <ul>
              <li><strong>Consent:</strong> Where you have given clear consent for specific purposes</li>
              <li><strong>Contract:</strong> When necessary to perform our service contract with you</li>
              <li><strong>Legal Obligation:</strong> To comply with applicable laws (e.g., PECA requirements)</li>
              <li><strong>Legitimate Interests:</strong> For our legitimate business interests, except where overridden by your rights</li>
            </ul>
          </div>
          <div class="legal-basis">
            <h3>For Pakistan Compliance:</h3>
            <ul>
              <li><strong>PECA 2016:</strong> For security measures and preventing unauthorized access</li>
              <li><strong>PDPB 2023 (when enacted):</strong> Based on consent, legitimate interests, or contractual necessity</li>
            </ul>
          </div>
        </div>
      </section>

      <section id="data-sharing">
        <h2>7. Data Sharing and Transfer</h2>
        <p>We may share your personal data with:</p>
        <ul>
          <li><strong>Service providers:</strong> Cloudflare (security), Google (analytics), Hotjar (user behavior)</li>
          <li><strong>Legal requirements:</strong> When required by law or to protect our rights</li>
          <li><strong>Business transfers:</strong> In connection with any merger, sale, or acquisition</li>
        </ul>
        <div class="cross-border-transfer">
          <h3>Cross-Border Data Transfers</h3>
          <p>Your data may be transferred to and processed in countries other than Pakistan. We ensure appropriate safeguards are in place, such as:</p>
          <ul>
            <li>Standard contractual clauses approved by relevant authorities</li>
            <li>Data processing agreements with recipients</li>
            <li>Adequacy decisions where applicable</li>
          </ul>
          <p>Once Pakistan's PDPB is enacted, we will comply with its requirements for cross-border data transfers.</p>
        </div>
      </section>

      <section id="data-security">
        <h2>8. Data Security</h2>
        <p>We implement appropriate technical and organizational measures to protect your personal data, including:</p>
        <ul>
          <li>Encryption in transit (HTTPS) and at rest</li>
          <li>Regular security assessments and penetration testing</li>
          <li>Access controls and authentication mechanisms</li>
          <li>Employee training on data protection practices</li>
        </ul>
        <p>Under PECA, we are required to protect data from unauthorized access and use.</p>
      </section>

      <section id="data-rights">
        <h2>9. Your Data Protection Rights</h2>
        <p>Depending on your location and applicable law, you have the following rights:</p>
        <div class="rights-grid">
          <div class="right" v-for="right in userRights" :key="right.title">
            <h4>{{ right.title }}</h4>
            <p>{{ right.desc }}</p>
          </div>
        </div>
      </section>

      <section id="data-retention">
        <h2>10. Data Retention</h2>
        <p>We retain your personal data only for as long as necessary to fulfill the purposes for which it was collected, including:</p>
        <ul>
          <li><strong>Active accounts:</strong> Duration of your account relationship</li>
          <li><strong>Usage data:</strong> Up to 26 months for analytics purposes</li>
          <li><strong>Support communications:</strong> Up to 3 years for quality assurance</li>
          <li><strong>Legal requirements:</strong> As required by law (e.g., under PECA)</li>
        </ul>
      </section>

      <section id="children-privacy">
        <h2>11. Children's Privacy</h2>
        <p>
          Our Service is not directed to individuals under the age of 16. We do not knowingly collect 
          personal data from children under 16. If we become aware that we have inadvertently collected 
          personal data from a child under 16, we will take steps to delete that information as quickly 
          as possible.
        </p>
      </section>

      <section id="policy-changes">
        <h2>12. Changes to This Policy</h2>
        <p>We may update this Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of any material changes by:</p>
        <ul>
          <li>Posting the new Policy on this page</li>
          <li>Updating the "Last updated" date</li>
          <li>Providing notice through our website or email</li>
        </ul>
      </section>

      <section id="contact">
        <h2>13. Contact Us</h2>
        <p>If you have any questions about this Policy or our data practices, please contact us:</p>
        <div class="contact-info">
          <p><strong>SiteClip Ltd.</strong><br>
          [Your Physical Address]<br>
          Pakistan<br>
          Email: privacy@siteclip.com<br>
          Phone: +92-XXX-XXXXXXX
          </p>
          <p>For GDPR-related inquiries, you may also contact your local supervisory authority.</p>
        </div>
      </section>

      <div id="read-more" class="read-more-section">
        <h3>Understanding Your Cookie Choices</h3>
        <p>
          Cookies can be "first-party" (set by us) or "third-party" (set by our partners). 
          They can also be "session" cookies (deleted when you close your browser) or 
          "persistent" cookies (stored on your device between sessions).
        </p>
        <div class="cookie-diagram">
          <h4>How Cookies Work on SiteClip</h4>
          <div class="b-w-diagram">
            <div class="diagram-node start">You Visit SiteClip</div>
            <div class="diagram-arrow">↓</div>
            <div class="diagram-node decision">Cookie Consent Given?</div>
            <div class="diagram-branches">
              <div class="branch">
                <div class="diagram-arrow">Yes ↓</div>
                <div class="diagram-node success">All Cookies Activated</div>
                <div class="diagram-arrow">↓</div>
                <div class="diagram-node end">Enhanced Experience & Personalization</div>
              </div>
              <div class="branch">
                <div class="diagram-arrow">No ↓</div>
                <div class="diagram-node fail">Only Essential Cookies Active</div>
                <div class="diagram-arrow">↓</div>
                <div class="diagram-node end">Basic Functionality Only</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Cookie Customization Modal -->
    <div v-if="showCustomize" class="modal-overlay" @click.self="showCustomize = false">
      <div class="modal-content">
        <h3>Customize Your Cookie Preferences</h3>
        <p>Select which cookie categories you wish to allow:</p>
        <div class="cookie-preferences">
          <div v-for="category in cookieCategories" :key="category.id" class="preference-item">
            <label class="switch">
              <input type="checkbox" :checked="category.enabled" @change="togglePreference(category.id)">
              <span class="slider"></span>
            </label>
            <span class="pref-name">{{ category.name }}</span>
            <span class="count">({{ category.count }})</span>
          </div>
        </div>
        <div class="modal-actions">
          <button @click="savePreferences" class="btn btn-primary">Save Preferences</button>
          <button @click="showCustomize = false" class="btn btn-secondary">Cancel</button>
        </div>
      </div>
    </div>
    
</div>
<Footer />
</template>

<style scoped>
/* --- Global Reset & Typography (Monochrome) --- */
.privacy-policy-container {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  line-height: 1.7;
  color: #111;
  background-color: #fff;
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}

h1 {
  font-size: 2.5rem;
  font-weight: 800;
  border-bottom: 4px solid #000;
  padding-bottom: 15px;
  margin-top: 0;
  letter-spacing: -1px;
}

h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-top: 50px;
  margin-bottom: 20px;
  padding-left: 15px;
  border-left: 5px solid #000;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

h3 {
  font-size: 1.2rem;
  font-weight: 700;
  margin-top: 30px;
  margin-bottom: 15px;
}

h4 {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 10px 0;
}

p {
  margin-bottom: 15px;
  color: #222;
}

ul {
  padding-left: 25px;
  margin-bottom: 20px;
}

li {
  margin-bottom: 10px;
}

a.link {
  color: #000;
  text-decoration: underline;
  text-underline-offset: 3px;
  font-weight: 600;
  transition: opacity 0.2s;
}

a.link:hover {
  opacity: 0.7;
}

.last-updated {
  color: #666;
  font-style: italic;
  margin-bottom: 40px;
  font-size: 0.9rem;
}

/* --- Cookie Banner --- */
.cookie-banner {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  background-color: #000;
  color: #fff;
  padding: 25px;
  border-radius: 0;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  z-index: 1000;
  max-width: 800px;
  width: 90%;
}

.cookie-content h3 {
  margin-top: 0;
  color: #fff;
  border-bottom: 1px solid #444;
  padding-bottom: 10px;
}

.cookie-actions {
  display: flex;
  gap: 15px;
  flex-wrap: wrap;
  margin-top: 15px;
}

/* --- Buttons --- */
.btn {
  padding: 10px 20px;
  border: 2px solid transparent;
  border-radius: 0;
  cursor: pointer;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 1px;
  transition: all 0.2s ease;
  background: transparent;
}

.btn-primary {
  background-color: #fff;
  color: #000;
  border-color: #fff;
}

.btn-primary:hover {
  background-color: #ddd;
  border-color: #ddd;
  color: #000;
}

.btn-secondary {
  background-color: transparent;
  color: #fff;
  border-color: #fff;
}

.btn-secondary:hover {
  background-color: #fff;
  color: #000;
}

.btn-outline {
  background-color: transparent;
  color: #aaa;
  border-color: #555;
}

.btn-outline:hover {
  border-color: #fff;
  color: #fff;
}

/* --- Content Sections --- */
.privacy-content {
  background-color: #fff;
  padding: 50px;
  border: 1px solid #e0e0e0;
}

.data-controller-details,
.contact-info {
  background-color: #f5f5f5;
  padding: 20px;
  border-left: 5px solid #000;
  border-radius: 0;
}

.data-table table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
  border: 1px solid #000;
}

.data-table th, 
.data-table td {
  padding: 15px;
  text-align: left;
  border: 1px solid #333;
}

.data-table th {
  background-color: #000;
  color: #fff;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}

.data-table tr:nth-child(even) {
  background-color: #f5f5f5;
}

/* --- Cookie Categories --- */
.cookie-categories {
  margin: 40px 0;
}

.cookie-category {
  margin-bottom: 15px;
  border: 1px solid #000;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 20px;
  background-color: #f5f5f5;
  cursor: pointer;
  transition: background-color 0.2s ease;
  font-weight: 600;
}

.category-header:hover {
  background-color: #e0e0e0;
}

.toggle-icon {
  font-size: 0.8rem;
}

.category-details {
  padding: 20px;
  border-top: 1px solid #000;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-5px); }
  to { opacity: 1; transform: translateY(0); }
}

.cookie-item {
  margin-top: 15px;
  padding: 15px;
  background-color: #fff;
  border: 1px solid #ccc;
  border-left: 5px solid #000;
}

/* --- Grid Layouts --- */
.legal-bases-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 20px;
  margin: 30px 0;
}

.legal-basis {
  background-color: #fff;
  padding: 25px;
  border: 1px solid #000;
  box-shadow: 5px 5px 0px #000;
}

.legal-basis h3 {
  margin-top: 0;
  border-bottom: 2px solid #000;
  padding-bottom: 10px;
}

.rights-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin: 30px 0;
}

.right {
  background-color: #fafafa;
  padding: 20px;
  border-top: 3px solid #000;
  transition: transform 0.2s, box-shadow 0.2s;
}

.right:hover {
  transform: translateY(-3px);
  box-shadow: 5px 5px 0px #000;
}

.right h4 {
  margin-top: 0;
  color: #000;
}

.cross-border-transfer {
  margin-top: 40px;
  padding: 25px;
  background-color: #000;
  color: #fff;
  border-radius: 0;
}

.cross-border-transfer h3 {
  color: #fff;
  border-bottom: 1px solid #555;
  padding-bottom: 10px;
}

.cross-border-transfer p, 
.cross-border-transfer li {
  color: #e0e0e0;
}

/* --- Read More Section --- */
.read-more-section {
  margin-top: 60px;
  padding: 40px;
  background-color: #f5f5f5;
  border: 2px solid #000;
}

.b-w-diagram {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-family: monospace;
  margin-top: 30px;
  padding: 20px;
  background: #fff;
  border: 1px solid #000;
}

.diagram-node {
  padding: 10px 20px;
  border: 2px solid #000;
  font-weight: bold;
  text-align: center;
  min-width: 200px;
  background: #fff;
}

.diagram-node.start, .diagram-node.end {
  background: #000;
  color: #fff;
}

.diagram-node.decision {
  border-style: dashed;
}

.diagram-arrow {
  font-size: 1.5rem;
  margin: 10px 0;
  color: #000;
}

.diagram-branches {
  display: flex;
  justify-content: space-around;
  width: 100%;
  gap: 20px;
  margin-top: 10px;
}

.branch {
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* --- Modal --- */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1001;
}

.modal-content {
  background-color: #fff;
  padding: 40px;
  border: 2px solid #000;
  width: 90%;
  max-width: 550px;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 15px 15px 0px rgba(0,0,0,1);
}

.modal-content h3 {
  margin-top: 0;
  text-transform: uppercase;
  border-bottom: 2px solid #000;
  padding-bottom: 10px;
}

/* --- Toggle Switches --- */
.cookie-preferences {
  margin: 30px 0;
}

.preference-item {
  display: flex;
  align-items: center;
  padding: 15px 0;
  border-bottom: 1px solid #ddd;
}

.switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 26px;
  margin-right: 20px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: .3s;
  border-radius: 0;
  border: 2px solid #000;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 2px;
  bottom: 2px;
  background-color: #fff;
  transition: .3s;
  border: 1px solid #000;
}

input:checked + .slider {
  background-color: #000;
}

input:checked + .slider:before {
  transform: translateX(24px);
  background-color: #fff;
}

.pref-name {
  font-weight: 600;
}

.count {
  margin-left: 10px;
  color: #888;
  font-size: 0.9rem;
}

.modal-actions {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
  margin-top: 30px;
  border-top: 1px solid #ddd;
  padding-top: 20px;
}

/* Modal Button Overrides */
.modal-actions .btn-primary {
  background-color: #000;
  color: #fff;
  border-color: #000;
}

.modal-actions .btn-primary:hover {
  background-color: #333;
}

.modal-actions .btn-secondary {
  color: #000;
  border-color: #000;
}

.modal-actions .btn-secondary:hover {
  background-color: #000;
  color: #fff;
}

/* --- Responsive --- */
@media (max-width: 768px) {
  .cookie-banner {
    left: 0;
    right: 0;
    transform: none;
    width: 100%;
    bottom: 0;
    border-radius: 0;
  }
  
  .privacy-content {
    padding: 25px 15px;
  }
  
  .legal-bases-grid,
  .rights-grid {
    grid-template-columns: 1fr;
  }
  
  .cookie-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    text-align: center;
  }

  .diagram-branches {
    flex-direction: column;
    align-items: center;
  }

  h1 {
    font-size: 2rem;
  }
}
</style>