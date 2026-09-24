/**
 * LHU PDF Document Annotator (Google Docs Style Highlight & Floating Comment + Box Area for Scans)
 * Balai K3 Surabaya
 */

window.LhuAnnotator = {
    instances: {},

    init: function(config) {
        const id = config.suketId;
        const container = document.getElementById(config.containerId);
        if (!container) return;

        if (this.instances[id] && this.instances[id].loadedUrl === config.pdfUrl && this.instances[id].pdf) {
            return;
        }

        const instance = {
            config: config,
            loadedUrl: config.pdfUrl,
            pdf: null,
            activeSelectionText: '',
            activeSelectionRange: null,
            activePageNumber: 1,
            pagesRendered: 0,
            isRendering: true,
            pendingHighlight: null,
            mode: 'text', // 'text' (default) or 'box'
        };
        this.instances[id] = instance;

        this.renderPdf(instance);
        if (!config.readOnly) {
            this.setupSelectionListener(instance);
        }
    },

    setMode: function(suketId, mode) {
        const instance = this.instances[suketId];
        if (!instance) return;

        instance.mode = (mode === 'box') ? 'box' : 'text';
        const container = document.getElementById(instance.config.containerId);
        if (container) {
            if (instance.mode === 'box') {
                container.classList.add('lhu-drawing-mode');
            } else {
                container.classList.remove('lhu-drawing-mode');
            }
        }

        // Hide floating text button when switching modes
        this.hideFloatingBtn(instance.config.floatingBtnId);

        // Update toolbar button states
        const toolbar = document.querySelector(`.lhu-annotator-toolbar[data-suket="${suketId}"]`) ||
                        (container ? container.parentElement.querySelector('.lhu-annotator-toolbar') : null);
        if (toolbar) {
            toolbar.querySelectorAll('.btn-mode').forEach(btn => {
                if (btn.dataset.mode === instance.mode) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });
        }
    },

    renderPdf: async function(instance) {
        const config = instance.config;
        const container = document.getElementById(config.containerId);
        if (!container) return;

        instance.isRendering = true;

        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-light" role="status"></div>
                <div class="small mt-2 text-white fw-semibold">Memuat dokumen LHU...</div>
            </div>
        `;

        try {
            if (!window.pdfjsLib) {
                throw new Error("PDF.js library is not loaded");
            }
            const workerUrl = window.location.origin + '/vendor/pdfjs/pdf.worker.min.js';
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

            const loadingTask = window.pdfjsLib.getDocument({
                url: config.pdfUrl,
                cMapUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/cmaps/',
                cMapPacked: true,
            });

            const pdf = await loadingTask.promise;
            instance.pdf = pdf;
            container.innerHTML = '';

            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                await this.renderPage(instance, pageNum, container);
            }

            // Apply highlights for all saved comments (both text & box areas)
            this.applySavedHighlights(instance);

            instance.isRendering = false;

            // Trigger pending scroll if user requested before render completed
            if (instance.pendingHighlight) {
                const ph = instance.pendingHighlight;
                instance.pendingHighlight = null;
                setTimeout(() => {
                    this.scrollToHighlight(config.suketId, ph.commentId, ph.pageHint);
                }, 100);
            }

        } catch (err) {
            instance.isRendering = false;
            console.warn("Gagal merender PDF via PDF.js:", err);
            container.innerHTML = `
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center mx-auto my-5" style="max-width: 500px; background: rgba(255,255,255,0.95);">
                    <i class="bi bi-file-earmark-exclamation fs-1 text-warning mb-2"></i>
                    <h6 class="fw-bold text-dark">Pratinjau Dokumen Memerlukan Pembaca Eksternal</h6>
                    <p class="small text-muted mb-3">
                        Format berkas ini tidak dapat dipratinjau langsung di dalam browser atau sedang diproses. Anda dapat mengunduh berkas untuk ditinjau.
                    </p>
                    <div>
                        <a href="${config.pdfUrl}" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold shadow-xs" download>
                            <i class="bi bi-download me-1"></i> Unduh Berkas Dokumen
                        </a>
                    </div>
                </div>
            `;
        }
    },

    renderPage: async function(instance, pageNum, container) {
        const page = await instance.pdf.getPage(pageNum);
        
        const unscaledViewport = page.getViewport({ scale: 1.0 });
        const containerWidth = container.clientWidth > 40 ? (container.clientWidth - 40) : 750;
        const scale = Math.min(Math.max(containerWidth / unscaledViewport.width, 0.9), 1.5);
        const viewport = page.getViewport({ scale: scale });

        const pageWrapper = document.createElement('div');
        pageWrapper.className = 'pdf-page-wrapper position-relative mx-auto mb-4 bg-white shadow rounded';
        pageWrapper.id = `pdf-page-${instance.config.suketId}-${pageNum}`;
        pageWrapper.dataset.pageNumber = pageNum;
        pageWrapper.dataset.suketId = instance.config.suketId;
        pageWrapper.style.width = `${viewport.width}px`;
        pageWrapper.style.height = `${viewport.height}px`;

        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        canvas.className = 'd-block';
        pageWrapper.appendChild(canvas);

        const renderContext = {
            canvasContext: canvas.getContext('2d'),
            viewport: viewport
        };
        await page.render(renderContext).promise;

        // Text Layer for selectable text & highlighting
        const textContent = await page.getTextContent();
        const textLayerDiv = document.createElement('div');
        textLayerDiv.className = 'textLayer position-absolute top-0 start-0';
        textLayerDiv.style.width = `${viewport.width}px`;
        textLayerDiv.style.height = `${viewport.height}px`;
        textLayerDiv.style.setProperty('--scale-factor', scale);
        pageWrapper.appendChild(textLayerDiv);

        if (window.pdfjsLib.renderTextLayer) {
            const textLayerTask = window.pdfjsLib.renderTextLayer({
                textContentSource: textContent,
                container: textLayerDiv,
                viewport: viewport,
                textDivs: []
            });
            if (textLayerTask && textLayerTask.promise) {
                await textLayerTask.promise;
            }
        }

        // Setup Box Drawing on this page if not read-only
        if (!instance.config.readOnly) {
            this.setupBoxDrawingOnPage(instance, pageWrapper, pageNum);
        }

        container.appendChild(pageWrapper);
    },

    /**
     * Setup drag-to-box annotation on scanned pages or arbitrary areas
     */
    setupBoxDrawingOnPage: function(instance, pageWrapper, pageNum) {
        pageWrapper.addEventListener('mousedown', (e) => {
            if (instance.mode !== 'box') return;

            // Only trigger on left click
            if (e.button !== 0) return;

            // Don't trigger if clicked on an existing comment box or badge
            if (e.target.closest('.lhu-saved-area-box') || e.target.closest('.lhu-area-badge')) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const pageRect = pageWrapper.getBoundingClientRect();
            const startX = Math.max(0, Math.min(pageRect.width, e.clientX - pageRect.left));
            const startY = Math.max(0, Math.min(pageRect.height, e.clientY - pageRect.top));

            const preview = document.createElement('div');
            preview.className = 'lhu-drag-preview-box';
            preview.style.left = `${startX}px`;
            preview.style.top = `${startY}px`;
            preview.style.width = '0px';
            preview.style.height = '0px';
            pageWrapper.appendChild(preview);

            let currentLeft = startX;
            let currentTop = startY;
            let currentWidth = 0;
            let currentHeight = 0;

            const onMouseMove = (moveEv) => {
                const currX = Math.max(0, Math.min(pageRect.width, moveEv.clientX - pageRect.left));
                const currY = Math.max(0, Math.min(pageRect.height, moveEv.clientY - pageRect.top));

                currentLeft = Math.min(startX, currX);
                currentTop = Math.min(startY, currY);
                currentWidth = Math.abs(currX - startX);
                currentHeight = Math.abs(currY - startY);

                preview.style.left = `${currentLeft}px`;
                preview.style.top = `${currentTop}px`;
                preview.style.width = `${currentWidth}px`;
                preview.style.height = `${currentHeight}px`;
            };

            const onMouseUp = (upEv) => {
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);

                if (preview.parentElement) {
                    preview.parentElement.removeChild(preview);
                }

                // Minimum 15px box to avoid accidental micro clicks
                if (currentWidth >= 15 && currentHeight >= 15) {
                    const leftPct = ((currentLeft / pageRect.width) * 100).toFixed(2);
                    const topPct = ((currentTop / pageRect.height) * 100).toFixed(2);
                    const widthPct = ((currentWidth / pageRect.width) * 100).toFixed(2);
                    const heightPct = ((currentHeight / pageRect.height) * 100).toFixed(2);

                    const boxCode = `[BOX:${pageNum},${leftPct}%,${topPct}%,${widthPct}%,${heightPct}%]`;

                    // Render active temporary box on this page
                    window.LhuAnnotator.renderTempBox(instance, pageNum, leftPct, topPct, widthPct, heightPct);

                    // Pre-fill selection data
                    window.LhuAnnotator.prefillSelectionData(instance, boxCode, pageNum);

                    // Scroll to comment form & focus comment input
                    const cardAdd = document.getElementById(`cardAddComment${instance.config.suketId}`) ||
                                    document.getElementById(`formAddComment${instance.config.suketId}`);
                    if (cardAdd) {
                        cardAdd.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                    if (instance.config.commentInputId) {
                        const el = document.getElementById(instance.config.commentInputId);
                        if (el) el.focus();
                    }
                }
            };

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        });
    },

    /**
     * Render temporary active preview box
     */
    renderTempBox: function(instance, pageNum, leftPct, topPct, widthPct, heightPct) {
        const suketId = instance.config.suketId;
        const prevTemp = document.getElementById(`temp-box-${suketId}`);
        if (prevTemp) prevTemp.remove();

        const pageWrapper = document.getElementById(`pdf-page-${suketId}-${pageNum}`);
        if (!pageWrapper) return;

        const tempBox = document.createElement('div');
        tempBox.id = `temp-box-${suketId}`;
        tempBox.className = 'lhu-saved-area-box is-active pulse-highlight';
        tempBox.style.left = `${leftPct}%`;
        tempBox.style.top = `${topPct}%`;
        tempBox.style.width = `${widthPct}%`;
        tempBox.style.height = `${heightPct}%`;
        tempBox.innerHTML = `<span class="lhu-area-badge bg-warning text-dark"><i class="fas fa-crosshairs me-1"></i>Area Ditandai</span>`;
        pageWrapper.appendChild(tempBox);
    },

    /**
     * Pre-fill the comment form inputs immediately when text or box is selected
     */
    prefillSelectionData: function(instance, text, pageNum) {
        const config = instance.config;
        if (!text || !text.trim()) return;

        const cleanText = text.trim();
        const validPage = pageNum || instance.activePageNumber || 1;

        instance.activeSelectionText = cleanText;
        instance.activePageNumber = validPage;

        const isBox = cleanText.startsWith('[BOX:');

        // Save into floating button DOM dataset for persistent fallback
        const floatingBtn = document.getElementById(config.floatingBtnId);
        if (floatingBtn) {
            floatingBtn.dataset.selectedText = cleanText;
            floatingBtn.dataset.pageNum = validPage;
        }

        // 1. Kutipan Teks / Nilai Data yang Salah
        if (config.highlightTextInputId) {
            const el = document.getElementById(config.highlightTextInputId);
            if (el) {
                el.value = cleanText;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // 2. Bagian / Halaman
        if (config.bagianInputId) {
            const el = document.getElementById(config.bagianInputId);
            if (el && (!el.value || el.value.startsWith('Halaman '))) {
                el.value = isBox ? `Halaman ${validPage} (Area Scan/Box)` : `Halaman ${validPage}`;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // 3. Chip Preview
        if (config.highlightPreviewId) {
            const el = document.getElementById(config.highlightPreviewId);
            if (el) {
                if (isBox) {
                    el.innerHTML = `<i class="fas fa-vector-square text-warning me-1"></i>Kotak Area Scan (Hal. ${validPage})`;
                } else {
                    el.textContent = cleanText.length > 70 ? (cleanText.substring(0, 70) + '...') : cleanText;
                }
            }
        }
        if (config.highlightBannerId) {
            const el = document.getElementById(config.highlightBannerId);
            if (el) el.style.display = 'flex';
        }
    },

    setupSelectionListener: function(instance) {
        const config = instance.config;
        const container = document.getElementById(config.containerId);
        const floatingBtn = document.getElementById(config.floatingBtnId);
        if (!container || !floatingBtn) return;

        // Move floating button to body so it floats freely without clipping or transform issues
        if (floatingBtn.parentElement !== document.body) {
            document.body.appendChild(floatingBtn);
        }

        const handleSelection = () => {
            if (instance.mode === 'box') return; // In box mode, don't trigger text selection popup

            const selection = window.getSelection();
            const text = selection ? selection.toString().trim() : '';

            if (text.length >= 2) {
                if (selection.rangeCount === 0) {
                    this.hideFloatingBtn(config.floatingBtnId);
                    return;
                }
                const range = selection.getRangeAt(0);

                let commonNode = range.commonAncestorContainer;
                if (commonNode.nodeType === Node.TEXT_NODE) {
                    commonNode = commonNode.parentElement;
                }

                if (!container.contains(commonNode)) {
                    this.hideFloatingBtn(config.floatingBtnId);
                    return;
                }

                const rect = range.getBoundingClientRect();
                if (rect.width === 0 || rect.height === 0) {
                    this.hideFloatingBtn(config.floatingBtnId);
                    return;
                }

                instance.activeSelectionText = text;
                instance.activeSelectionRange = range.cloneRange();

                // Find which page this belongs to
                let node = commonNode;
                while (node && (!node.dataset || !node.dataset.pageNumber)) {
                    node = node.parentElement;
                }
                const pageNum = node ? (parseInt(node.dataset.pageNumber, 10) || 1) : 1;
                instance.activePageNumber = pageNum;

                // IMMEDIATELY pre-fill the form so it is never empty!
                this.prefillSelectionData(instance, text, pageNum);

                const btnWidth = 145;
                const left = Math.max(10, Math.min(window.innerWidth - btnWidth - 10, rect.left + (rect.width / 2) - (btnWidth / 2)));
                const top = Math.max(10, rect.top - 46);

                floatingBtn.style.position = 'fixed';
                floatingBtn.style.zIndex = '2050';
                floatingBtn.style.left = `${left}px`;
                floatingBtn.style.top = `${top}px`;
                floatingBtn.style.display = 'block';

            } else {
                this.hideFloatingBtn(config.floatingBtnId);
            }
        };

        container.addEventListener('mouseup', handleSelection);
        container.addEventListener('touchend', handleSelection);
        container.addEventListener('keyup', handleSelection);

        document.addEventListener('mousedown', (e) => {
            if (floatingBtn.contains(e.target) || container.contains(e.target)) return;

            // If user clicked inside the comment form on the right, keep active selection text safe
            const formAdd = document.getElementById(`formAddComment${config.suketId}`) || document.getElementById(`cardAddComment${config.suketId}`);
            if (formAdd && formAdd.contains(e.target)) {
                this.hideFloatingBtn(config.floatingBtnId);
                return;
            }

            this.hideFloatingBtn(config.floatingBtnId);
        });
    },

    hideFloatingBtn: function(floatingBtnId) {
        const btn = document.getElementById(floatingBtnId);
        if (btn) btn.style.display = 'none';
    },

    onFloatingCommentClick: function(suketId) {
        const instance = this.instances[suketId];
        if (!instance) return;

        const config = instance.config;
        const floatingBtn = document.getElementById(config.floatingBtnId);

        // Retrieve text from instance, fallback to floatingBtn dataset, fallback to current selection
        let selectedText = instance.activeSelectionText;
        let pageNum = instance.activePageNumber;

        if ((!selectedText || !selectedText.trim()) && floatingBtn && floatingBtn.dataset.selectedText) {
            selectedText = floatingBtn.dataset.selectedText;
            pageNum = parseInt(floatingBtn.dataset.pageNum, 10) || 1;
        }

        if (!selectedText || !selectedText.trim()) {
            const sel = window.getSelection();
            if (sel && sel.toString().trim()) {
                selectedText = sel.toString().trim();
            }
        }

        this.hideFloatingBtn(config.floatingBtnId);

        // Pre-fill fields guaranteed
        if (selectedText) {
            this.prefillSelectionData(instance, selectedText, pageNum);
        }

        // Smooth scroll to form card
        const cardAdd = document.getElementById(`cardAddComment${suketId}`) || document.getElementById(`formAddComment${suketId}`);
        if (cardAdd) {
            cardAdd.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Focus & scroll to comment textarea
        if (config.commentInputId) {
            const el = document.getElementById(config.commentInputId);
            if (el) {
                el.focus();
            }
        }

        // Apply visible active selection highlight on document
        this.highlightActiveSelection(instance, suketId);
    },

    highlightActiveSelection: function(instance, suketId) {
        // Clear previous active temporary highlight
        this.clearActiveSelection(suketId);

        const container = document.getElementById(instance.config.containerId);
        if (!container || !instance.activeSelectionText) return;

        if (instance.activeSelectionText.startsWith('[BOX:')) return; // Box preview already handled

        const searchStr = instance.activeSelectionText.trim();
        const spans = container.querySelectorAll('.textLayer > span');
        let matched = false;

        spans.forEach(span => {
            if (matched) return;
            const text = span.textContent;
            const matchPos = text.toLowerCase().indexOf(searchStr.toLowerCase());
            if (matchPos !== -1) {
                matched = true;
                const before = text.substring(0, matchPos);
                const match = text.substring(matchPos, matchPos + searchStr.length);
                const after = text.substring(matchPos + searchStr.length);

                span.innerHTML = `${escapeHtml(before)}<mark class="lhu-active-selection font-monospace" id="temp-selection-${suketId}">${escapeHtml(match)}</mark>${escapeHtml(after)}`;
            }
        });

        // Fallback: If not exact in one span, highlight spans intersecting the range
        if (!matched && instance.activeSelectionRange) {
            try {
                const mark = document.createElement('mark');
                mark.className = 'lhu-active-selection font-monospace';
                mark.id = `temp-selection-${suketId}`;
                instance.activeSelectionRange.surroundContents(mark);
            } catch (e) {
                const startNode = instance.activeSelectionRange.startContainer;
                const parentSpan = startNode.parentElement;
                if (parentSpan && parentSpan.classList.contains('textLayer')) {
                    parentSpan.classList.add('lhu-active-selection');
                } else if (parentSpan && parentSpan.parentElement && parentSpan.parentElement.classList.contains('textLayer')) {
                    parentSpan.classList.add('lhu-active-selection');
                    parentSpan.id = `temp-selection-${suketId}`;
                }
            }
        }
    },

    clearActiveSelection: function(suketId) {
        const instance = this.instances[suketId];
        if (!instance) return;

        const config = instance.config;
        if (config.highlightTextInputId) {
            const el = document.getElementById(config.highlightTextInputId);
            if (el) el.value = '';
        }
        if (config.highlightBannerId) {
            const el = document.getElementById(config.highlightBannerId);
            if (el) el.style.display = 'none';
        }

        // Remove temp box
        const tempBox = document.getElementById(`temp-box-${suketId}`);
        if (tempBox) tempBox.remove();

        const tempMark = document.getElementById(`temp-selection-${suketId}`);
        if (tempMark) {
            if (tempMark.tagName.toLowerCase() === 'mark') {
                const parent = tempMark.parentNode;
                while (tempMark.firstChild) parent.insertBefore(tempMark.firstChild, tempMark);
                parent.removeChild(tempMark);
            } else {
                tempMark.classList.remove('lhu-active-selection');
                tempMark.removeAttribute('id');
            }
        }

        const container = document.getElementById(config.containerId);
        if (container) {
            container.querySelectorAll('.lhu-active-selection').forEach(el => {
                el.classList.remove('lhu-active-selection');
            });
        }
    },

    applySavedHighlights: function(instance) {
        const config = instance.config;
        const container = document.getElementById(config.containerId);
        if (!container || !config.comments) return;

        const comments = Array.isArray(config.comments) ? config.comments : Object.values(config.comments);

        // Clear existing saved boxes to prevent duplicates upon re-render
        container.querySelectorAll('.lhu-saved-area-box').forEach(el => {
            if (!el.id || !el.id.startsWith('temp-box-')) el.remove();
        });

        // Box regex: [BOX:pageNum,left%,top%,width%,height%]
        const boxRegex = /\[BOX:(\d+),\s*([\d.]+)%?,\s*([\d.]+)%?,\s*([\d.]+)%?,\s*([\d.]+)%?\]/i;

        comments.forEach((cmt, idx) => {
            if (!cmt.highlight_text || !cmt.highlight_text.trim()) return;
            const searchStr = cmt.highlight_text.trim();
            const commentId = cmt.id;
            const markerNum = idx + 1;

            // 1. CHECK IF BOX ANNOTATION
            const boxMatch = searchStr.match(boxRegex);
            if (boxMatch) {
                const pageNum = parseInt(boxMatch[1], 10);
                const leftPct = parseFloat(boxMatch[2]);
                const topPct = parseFloat(boxMatch[3]);
                const widthPct = parseFloat(boxMatch[4]);
                const heightPct = parseFloat(boxMatch[5]);

                const pageWrapper = document.getElementById(`pdf-page-${instance.config.suketId}-${pageNum}`);
                if (pageWrapper) {
                    const boxEl = document.createElement('div');
                    boxEl.className = 'lhu-saved-area-box';
                    boxEl.dataset.commentId = commentId;
                    boxEl.style.left = `${leftPct}%`;
                    boxEl.style.top = `${topPct}%`;
                    boxEl.style.width = `${widthPct}%`;
                    boxEl.style.height = `${heightPct}%`;
                    boxEl.title = `Area Kesalahan #${markerNum}: ${cmt.comment}`;
                    boxEl.innerHTML = `<span class="lhu-area-badge"><i class="fas fa-vector-square me-1"></i>#${markerNum}</span>`;

                    boxEl.addEventListener('click', () => {
                        window.LhuAnnotator.scrollToCommentCard(instance.config.suketId, commentId);
                    });

                    pageWrapper.appendChild(boxEl);
                }
                return; // Done for this comment
            }

            // 2. TEXT LAYER SEARCH (High contrast highlights)
            const spans = container.querySelectorAll('.textLayer > span');
            let matched = false;

            // Direct span match
            spans.forEach(span => {
                if (matched) return;
                const text = span.textContent;
                const matchPos = text.toLowerCase().indexOf(searchStr.toLowerCase());
                if (matchPos !== -1) {
                    matched = true;
                    const before = text.substring(0, matchPos);
                    const match = text.substring(matchPos, matchPos + searchStr.length);
                    const after = text.substring(matchPos + searchStr.length);

                    span.innerHTML = `${escapeHtml(before)}<mark class="lhu-saved-highlight" data-comment-id="${commentId}" title="Sorotan Kesalahan #${markerNum}: ${escapeHtml(cmt.comment)}" style="cursor: pointer;">${escapeHtml(match)}<span class="badge bg-danger rounded-pill ms-1" style="font-size: 9px; vertical-align: super;">#${markerNum}</span></mark>${escapeHtml(after)}`;
                    
                    const markEl = span.querySelector(`mark[data-comment-id="${commentId}"]`);
                    if (markEl) {
                        markEl.addEventListener('click', () => {
                            window.LhuAnnotator.scrollToCommentCard(instance.config.suketId, commentId);
                        });
                    }
                }
            });

            // Normalized whitespace match
            if (!matched) {
                const normSearch = searchStr.replace(/\s+/g, ' ').toLowerCase();
                spans.forEach(span => {
                    if (matched) return;
                    const normText = span.textContent.replace(/\s+/g, ' ').toLowerCase();
                    const matchPos = normText.indexOf(normSearch);
                    if (matchPos !== -1) {
                        matched = true;
                        span.innerHTML = `<mark class="lhu-saved-highlight" data-comment-id="${commentId}" title="Sorotan Kesalahan #${markerNum}: ${escapeHtml(cmt.comment)}" style="cursor: pointer;">${escapeHtml(span.textContent)}<span class="badge bg-danger rounded-pill ms-1" style="font-size: 9px; vertical-align: super;">#${markerNum}</span></mark>`;
                        const markEl = span.querySelector(`mark[data-comment-id="${commentId}"]`);
                        if (markEl) {
                            markEl.addEventListener('click', () => {
                                window.LhuAnnotator.scrollToCommentCard(instance.config.suketId, commentId);
                            });
                        }
                    }
                });
            }

            // Match first word phrase fallback
            if (!matched && searchStr.length > 4) {
                const words = searchStr.split(/\s+/).filter(w => w.length > 3);
                if (words.length > 0) {
                    const firstWord = words[0];
                    spans.forEach(span => {
                        if (matched) return;
                        const matchPos = span.textContent.toLowerCase().indexOf(firstWord.toLowerCase());
                        if (matchPos !== -1) {
                            matched = true;
                            const text = span.textContent;
                            const before = text.substring(0, matchPos);
                            const match = text.substring(matchPos, matchPos + firstWord.length);
                            const after = text.substring(matchPos + firstWord.length);

                            span.innerHTML = `${escapeHtml(before)}<mark class="lhu-saved-highlight" data-comment-id="${commentId}" title="Sorotan Kesalahan #${markerNum}: ${escapeHtml(cmt.comment)}" style="cursor: pointer;">${escapeHtml(match)}<span class="badge bg-danger rounded-pill ms-1" style="font-size: 9px; vertical-align: super;">#${markerNum}</span></mark>${escapeHtml(after)}`;

                            const markEl = span.querySelector(`mark[data-comment-id="${commentId}"]`);
                            if (markEl) {
                                markEl.addEventListener('click', () => {
                                    window.LhuAnnotator.scrollToCommentCard(instance.config.suketId, commentId);
                                });
                            }
                        }
                    });
                }
            }
        });
    },

    scrollToHighlight: function(suketId, commentId, pageHint, attempt = 0) {
        const instance = this.instances[suketId];

        // 1. Check for saved Box annotation element first
        const box = document.querySelector(`#evalPdfContainer${suketId} .lhu-saved-area-box[data-comment-id="${commentId}"], #evalUserPdfContainer${suketId} .lhu-saved-area-box[data-comment-id="${commentId}"]`);
        if (box) {
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
            box.classList.add('pulse-highlight');
            setTimeout(() => box.classList.remove('pulse-highlight'), 3000);
            return;
        }

        // 2. Search for specific highlighted text element
        const mark = document.querySelector(`#evalPdfContainer${suketId} mark[data-comment-id="${commentId}"], #evalUserPdfContainer${suketId} mark[data-comment-id="${commentId}"]`);
        if (mark) {
            mark.scrollIntoView({ behavior: 'smooth', block: 'center' });
            mark.classList.add('pulse-highlight');
            setTimeout(() => mark.classList.remove('pulse-highlight'), 3000);
            return;
        }

        // 3. If PDF is still loading / rendering, retry asynchronously
        if (instance && (instance.isRendering || !instance.pdf)) {
            instance.pendingHighlight = { commentId, pageHint };
            if (attempt < 15) {
                setTimeout(() => this.scrollToHighlight(suketId, commentId, pageHint, attempt + 1), 150);
                return;
            }
        }

        // 4. Fallback: Parse target page from pageHint (e.g. "Halaman 2" or "Hal 2")
        let targetPage = 1;
        if (pageHint) {
            const match = String(pageHint).match(/(?:halaman|hal\.?|page)\s*(\d+)/i);
            if (match) {
                targetPage = parseInt(match[1], 10);
            }
        }

        const pageEl = document.querySelector(`#pdf-page-${suketId}-${targetPage}`);
        if (pageEl) {
            pageEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            pageEl.classList.add('pulse-page-highlight');
            setTimeout(() => pageEl.classList.remove('pulse-page-highlight'), 3500);
        } else if (attempt < 10) {
            setTimeout(() => this.scrollToHighlight(suketId, commentId, pageHint, attempt + 1), 200);
        }
    },

    scrollToCommentCard: function(suketId, commentId) {
        const card = document.getElementById(`comment-card-${suketId}-${commentId}`) || 
                     document.getElementById(`user-comment-card-${suketId}-${commentId}`) ||
                     document.getElementById(`user-modal-comment-card-${suketId}-${commentId}`);
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            card.classList.add('border-danger', 'shadow-sm', 'bg-warning-subtle');
            setTimeout(() => {
                card.classList.remove('border-danger', 'shadow-sm', 'bg-warning-subtle');
            }, 2500);
        }
    }
};

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
