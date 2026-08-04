<template>
  <div class="folder">
    <div class="folder-label">100+ Integrations</div>

    <div class="icon-row">
      <!-- Gmail -->
      <div class="icon-tile">
        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
          <path fill="#4285F4" d="M6 8h36c1.1 0 2 .9 2 2v3l-20 13L4 13v-3c0-1.1.9-2 2-2z"/>
          <path fill="#EA4335" d="M4 13l20 13L44 13v3L24 29 4 16z"/>
          <path fill="#34A853" d="M4 14v24c0 1.1.9 2 2 2h4V18z"/>
          <path fill="#FBBC05" d="M44 14v24c0 1.1-.9 2-2 2h-4V18z"/>
          <path fill="#EA4335" d="M8 40h4V18l-4-3v25zM40 40h-4V18l4-3v25z"/>
        </svg>
      </div>

      <!-- Slack -->
      <div class="icon-tile">
        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
          <path fill="#36C5F0" d="M18.6 6.4a3.6 3.6 0 1 0 0 7.2h3.6V6.4z"/>
          <path fill="#2EB67D" d="M6.4 18.6a3.6 3.6 0 1 0 0 7.2h11.2v-7.2H6.4z"/>
          <path fill="#ECB22E" d="M29.4 6.4v11.2h7.2a3.6 3.6 0 1 0 0-7.2h-3.6V6.4a3.6 3.6 0 0 0-3.6-4z"/>
          <path fill="#E01E5A" d="M29.4 29.4a3.6 3.6 0 1 0 7.2 0v-3.6h-7.2z"/>
          <path fill="#36C5F0" d="M25.8 6.4h-7.2v11.2h7.2z"/>
          <path fill="#2EB67D" d="M6.4 25.8h11.2v7.2H6.4a3.6 3.6 0 1 1 0-7.2z"/>
          <path fill="#ECB22E" d="M25.8 29.4h7.2v-11.2h-7.2z"/>
          <path fill="#E01E5A" d="M22.2 41.6a3.6 3.6 0 1 0 0-7.2h-3.6v3.6a3.6 3.6 0 0 0 3.6 3.6z"/>
          <path fill="#E01E5A" d="M14.6 29.4h7.2v7.2h-7.2z"/>
        </svg>
      </div>

      <!-- Google Calendar -->
      <div class="icon-tile">
        <svg viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
          <rect x="6" y="9" width="36" height="33" rx="3" fill="#fff" stroke="#1A73E8" stroke-width="2"/>
          <rect x="6" y="9" width="36" height="9" fill="#1A73E8"/>
          <text x="24" y="33" font-family="Arial, sans-serif" font-size="15" font-weight="700" fill="#1A73E8" text-anchor="middle">31</text>
          <rect x="14" y="5" width="3" height="8" rx="1.5" fill="#1A73E8"/>
          <rect x="31" y="5" width="3" height="8" rx="1.5" fill="#1A73E8"/>
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup>
// Pure presentational card — pass props later if you want
// the label or icon set to be dynamic.
</script>

<style scoped>
.folder{
  --folder-bg: #f4f4f6;
  --text-dark: #1f2330;
  --tile-bg: #ffffff;
  --tile-shadow: rgba(20,20,40,0.10);
  --card-shadow: rgba(30,30,60,0.14);

  position: relative;
  width: 320px;
  height: 210px;
  background: var(--folder-bg);
  border-radius: 0px 18px 18px 18px;
  box-shadow:
    0 30px 60px -15px var(--card-shadow),
    0 10px 20px -10px rgba(30,30,60,0.10);
}

.folder::before{
  content: "";
  position: absolute;
  top: -22px;
  left: 0px;
  width: 90px;
  height: 28px;
  background: var(--folder-bg);
  border-radius: 12px 12px 0 0;
}

.folder-label{
  position: absolute;
  top: 28px;
  left: 28px;
  right: 28px;
  font-size: 17px;
  font-weight: 600;
  color: var(--text-dark);
  letter-spacing: -0.01em;
}

.icon-row{
  position: absolute;
  bottom: 56px;
  left: 0;
  right: 0;
  display: flex;
  justify-content: center;
  gap: 16px;
}

.icon-tile{
  width: 64px;
  height: 64px;
  background: var(--tile-bg);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow:
    0 12px 20px -6px var(--tile-shadow),
    0 2px 4px rgba(20,20,40,0.06);
}

.icon-tile svg{
  width: 36px;
  height: 36px;
  display: block;
}

.icon-tile:nth-child(1){ transform: translateY(14px) rotate(-3deg); }
.icon-tile:nth-child(2){ transform: translateY(2px)  rotate(0deg); }
.icon-tile:nth-child(3){ transform: translateY(14px) rotate(3deg); }
</style>
