function extractPlainText(pre: HTMLElement, button?: HTMLButtonElement): string {
	return Array.from(pre.childNodes)
		.filter((node) => node !== button)
		.map((node) =>
			node.nodeType === Node.TEXT_NODE
				? node.textContent || ''
				: node.nodeType === Node.ELEMENT_NODE
				? (node as HTMLElement).textContent || ''
				: ''
		)
		.join('')
		.trim()
}

const COPY_ICON =
	`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg>`

const CHECK_ICON =
	`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7"/></svg>`

/**
 * Homepage snippets render their filename in a header bar, which leaves an
 * empty right-hand side — a natural home for copy. These blocks live outside
 * `.prose`, so they aren't covered by the copy handling below.
 *
 * A snippet that isn't a file has no header, so its button floats over the
 * top-right of the block instead. Either way every block is copyable.
 */
document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll<HTMLElement>('.home-code-block').forEach((block) => {
		const title = block.querySelector('.code-title')
		const pre = block.querySelector('pre')
		if (!pre) {
			return
		}

		const button = document.createElement('button')
		button.type = 'button'
		button.className = title ? 'code-title-copy' : 'code-title-copy code-floating-copy'
		button.innerHTML = COPY_ICON
		button.setAttribute('aria-label', title ? `Copy ${title.textContent?.trim()}` : 'Copy code')
		;(title ?? block).appendChild(button)

		let reset: ReturnType<typeof setTimeout>

		button.addEventListener('click', () => {
			navigator.clipboard
				.writeText(extractPlainText(pre))
				.then(() => {
					button.setAttribute('data-copied', 'true')
					button.innerHTML = CHECK_ICON
					clearTimeout(reset)
					reset = setTimeout(() => {
						button.removeAttribute('data-copied')
						button.innerHTML = COPY_ICON
					}, 2000)
				})
				.catch((err) => console.error('Copy failed', err))
		})
	})
})

document.addEventListener('DOMContentLoaded', () => {
	const template = document.getElementById('copy-template') as HTMLTemplateElement | null
	if (!template) {
		return
	}

	document.querySelectorAll<HTMLPreElement>('.prose pre').forEach((pre) => {
		const copyButton = (template.content.cloneNode(true) as DocumentFragment).querySelector('button') as
			| HTMLButtonElement
			| null
		if (!copyButton) {
			return
		}

		pre.classList.add('relative', 'group')
		pre.appendChild(copyButton)

		copyButton.addEventListener('click', () => {
			const content = extractPlainText(pre, copyButton)
			navigator.clipboard
				.writeText(content)
				.then(() => {
					copyButton.setAttribute('data-copied', 'true')
					setTimeout(() => copyButton.removeAttribute('data-copied'), 2000)
				})
				.catch((err) => console.error('Copy failed', err))
		})
	})
})

document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll<HTMLPreElement>('button[data-copy]').forEach((button) => {
		button.addEventListener('click', () => {
			const target = document.querySelector(button.dataset.copy!) as HTMLElement

			if (!target) {
				return
			}

			const content = extractPlainText(target)

			navigator.clipboard
				.writeText(content)
				.then(() => {
					button.setAttribute('data-copied', 'true')
					setTimeout(() => button.removeAttribute('data-copied'), 2000)
				})
				.catch((err) => console.error('Copy failed', err))
		})
	})
})
