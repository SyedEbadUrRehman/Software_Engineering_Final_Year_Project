import { onMounted, onBeforeUnmount } from 'vue'
import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

/**
 * useScrollIconText
 * Animates every "icon + text" pair inside `rootRef` on scroll using a
 * scrub-driven timeline. Because the timeline's progress is tied directly
 * to scroll position (scrub), it:
 *   - plays forward as the marker enters the trigger zone (enter)
 *   - reverses smoothly if you scroll back up (leave)
 *   - replays automatically the moment you scroll back down into it (enterBack)
 * No manual reset logic is needed — scrub IS the seamless loop.
 *
 * Markup contract (class-based, works with any component):
 *   <div class="gsap-item">
 *     <div class="gsap-icon">...svg/img...</div>
 *     <div class="gsap-text">...title/desc...</div>
 *   </div>
 *
 * @param {import('vue').Ref<HTMLElement>} rootRef - container ref holding .gsap-item children
 * @param {object} opts
 *   itemSelector  - selector for each icon+text group (default '.gsap-item')
 *   iconSelector  - selector for the icon within a group (default '.gsap-icon')
 *   textSelector  - selector for the text within a group (default '.gsap-text')
 *   start         - ScrollTrigger start (default 'top 85%')
 *   end           - ScrollTrigger end   (default 'top 40%')
 *   scrub         - scrub smoothing in seconds (default 0.6)
 *   stagger       - delay between icon and text animating (default 0.15)
 *   markers       - show ScrollTrigger debug markers (default false)
 */
export function useScrollIconText(rootRef, opts = {}, customOpts = []) {

  const {
    itemSelector = '.gsap-item',
    iconSelector = '.gsap-icon',
    textSelector = '.gsap-text',
    paraSelector = '.gsap-para',
    headingSelector = '.gsap-heading',
    start = 'top 85%',
    end = 'top 40%',
    scrub = 0.6,
    stagger = 0.15,
    markers = false,
  } = opts

  let ctx

  onMounted(() => {
    if (!rootRef.value) return
    textTransformation(rootRef.value)
    ctx = gsap.context(() => {
      const items = gsap.utils.toArray(itemSelector, rootRef.value)

      items.forEach((item, i) => {
        
        const icon = item.querySelectorAll(iconSelector)
        const text = item.querySelectorAll(textSelector)
        const para = item.querySelectorAll(paraSelector + " span")
        const heading = item.querySelectorAll(headingSelector + " span")
        if (!icon.length && !text.length && !para.length && !heading.length) return

        // Set a clean starting state so the very first paint (before
        // ScrollTrigger calculates) doesn't flash the final state.
        // NOTE: gsap.set() warns "GSAP target not found" if given an empty
        // array, so only call it when there's actually something to set.
        const iconTextHeading = [...icon, ...text, ...heading].filter(Boolean)
        const paraOnly = [...para].filter(Boolean)
        if (iconTextHeading.length) gsap.set(iconTextHeading, { opacity: 0 })
        if (paraOnly.length) gsap.set(paraOnly, { opacity: 0.3 })

        // Find a per-item override for this index, if one was passed in.
        // customOpts may be shorter than `items` (or empty entirely), so we
        // look up by `indexNo` rather than assuming customOpts[i] exists.
        const custom = customOpts.find((o) => o && o.indexNo === i)

        const tl = gsap.timeline({
          scrollTrigger: {
            trigger: item,
            start: custom ? custom.start : start,
            end: custom ? custom.end : end,
            scrub: custom ? 1.8 : scrub,
            // markers are dev-only — flip to true while tuning pin points
            markers,
            // invalidateOnRefresh keeps it correct on resize
            invalidateOnRefresh: true,
          },
        })

        if (heading.length) {

          tl.fromTo(
            heading,
            { opacity: 0, y: -100 },
            { opacity: 1, y: 0, ease: 'power2.out', duration: 4, stagger: { amount: 0.7 } }

          )


        }
        if (para.length) {

          tl.fromTo(
            para,
            { opacity: 0.3 },
            { opacity: 1, ease: 'power2.out', duration: 0.2, stagger: { amount: 0.9 } }

          )


        }
        if (icon.length) {

          tl.fromTo(
            icon,
            { opacity: 0, scale: 0.6, y: 30, rotate: -8, duration: 1 },
            { opacity: 1, scale: 1, y: 0, rotate: 0, ease: 'power2.out', duration: 0.8, stagger: 0.4 }

          )


        }

        if (text.length) {
          tl.fromTo(
            text,
            { opacity: 0, y: 24, duration: 1.5 },
            { opacity: 1, y: 0, ease: 'power2.out', duration: 0.8, stagger: 0.5 }

          )
        }
      })
    }, rootRef.value)
  })

  onBeforeUnmount(() => {
    ctx && ctx.revert()
  })




  function textTransformation(root) {

    // Inline-block is required here: CSS transforms (y, scale, rotate)
    // do NOT apply to `display: inline` elements, only opacity does.
    // Plain <span> is inline by default, which is why the heading/para
    // words were fading but never actually translating.
    const splitSpanStyle = 'display:inline-block;'

    // heading break in  to words

    const headings = root.querySelectorAll(headingSelector);

    headings.forEach(heading => {
      heading.style.wordBreak = "break-word";
      const words = heading.textContent.trim().split(/\s+/);

      heading.innerHTML = words
        .map(word => `<span style="${splitSpanStyle}">${word}</span>`)
        .join(`<span style="${splitSpanStyle}">&nbsp;</span>`);
    });


    // para break  in  to letters


    const paragraphs = root.querySelectorAll(paraSelector);

    paragraphs.forEach(para => {
      para.style.wordBreak = "break-all";
      const text = para.innerHTML;
      let result = "";

      for (let i = 0; i < text.length; i++) {
        const char = text[i];

        // Preserve <br>
        if (text.substring(i, i + 4).toLowerCase() === "<br") {
          const end = text.indexOf(">", i);
          result += text.substring(i, end + 1);
          i = end;
          continue;
        }

        // Preserve spaces
        if (char === " ") {
          result += `<span style="${splitSpanStyle}">&nbsp;</span>`;
        } else {
          result += `<span style="${splitSpanStyle}">${char}</span>`;
        }
      }

      para.innerHTML = result;
    });


  }

}