/**
 * UTILITY HELPERS - SHARED FUNCTIONS FOR SVELTE COMPONENTS
 * ========================================================
 * 
 * This file contains reusable utility functions that are used across
 * multiple Svelte components in our educational blog application.
 * 
 * EDUCATIONAL CONCEPTS COVERED:
 * - JavaScript utility functions
 * - Date formatting and manipulation
 * - String manipulation and validation
 * - Form validation helpers
 * - URL and slug generation
 * - Number formatting
 * - Error handling patterns
 * 
 * ORGANIZATION:
 * - Date and time utilities
 * - String manipulation
 * - Validation functions
 * - Formatting helpers
 * - DOM utilities
 * - Async helpers
 */

// =======================================================================
// DATE AND TIME UTILITIES
// =======================================================================

/**
 * FORMAT DATE FOR DISPLAY
 * =======================
 * 
 * Convert a date string or Date object to a human-readable format.
 * Handles various input formats and provides consistent output.
 * 
 * @param {string|Date|null} date - Date to format
 * @param {string} format - Format type ('relative', 'short', 'long', 'iso')
 * @returns {string} Formatted date string
 * 
 * EXAMPLES:
 * formatDate('2024-03-15') → "March 15, 2024"
 * formatDate(new Date(), 'relative') → "2 hours ago"
 * formatDate('2024-03-15', 'short') → "Mar 15"
 */
export function formatDate(date, format = 'long') {
  if (!date) return 'No date'
  
  try {
    const dateObj = typeof date === 'string' ? new Date(date) : date
    
    // Check if date is valid
    if (isNaN(dateObj.getTime())) {
      return 'Invalid date'
    }
    
    const now = new Date()
    
    switch (format) {
      case 'relative':
        return formatRelativeDate(dateObj, now)
      
      case 'short':
        return dateObj.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric'
        })
      
      case 'long':
        return dateObj.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        })
      
      case 'iso':
        return dateObj.toISOString().split('T')[0]
      
      case 'time':
        return dateObj.toLocaleTimeString('en-US', {
          hour: 'numeric',
          minute: '2-digit',
          hour12: true
        })
      
      case 'datetime':
        return dateObj.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric',
          hour: 'numeric',
          minute: '2-digit',
          hour12: true
        })
      
      default:
        return dateObj.toLocaleDateString('en-US')
    }
  } catch (error) {
    console.error('Error formatting date:', error)
    return 'Invalid date'
  }
}

/**
 * FORMAT RELATIVE DATE
 * ===================
 * 
 * Convert a date to relative format like "2 hours ago" or "in 3 days".
 * 
 * @param {Date} date - Date to format
 * @param {Date} now - Current date (for testing)
 * @returns {string} Relative date string
 */
function formatRelativeDate(date, now = new Date()) {
  const diffMs = now.getTime() - date.getTime()
  const diffSeconds = Math.floor(diffMs / 1000)
  const diffMinutes = Math.floor(diffSeconds / 60)
  const diffHours = Math.floor(diffMinutes / 60)
  const diffDays = Math.floor(diffHours / 24)
  const diffWeeks = Math.floor(diffDays / 7)
  const diffMonths = Math.floor(diffDays / 30)
  const diffYears = Math.floor(diffDays / 365)
  
  if (diffSeconds < 60) {
    return 'just now'
  } else if (diffMinutes < 60) {
    return `${diffMinutes} minute${diffMinutes !== 1 ? 's' : ''} ago`
  } else if (diffHours < 24) {
    return `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`
  } else if (diffDays < 7) {
    return `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`
  } else if (diffWeeks < 4) {
    return `${diffWeeks} week${diffWeeks !== 1 ? 's' : ''} ago`
  } else if (diffMonths < 12) {
    return `${diffMonths} month${diffMonths !== 1 ? 's' : ''} ago`
  } else {
    return `${diffYears} year${diffYears !== 1 ? 's' : ''} ago`
  }
}

/**
 * GET READING TIME ESTIMATE
 * =========================
 * 
 * Calculate estimated reading time for text content.
 * 
 * @param {string} text - Text content to analyze
 * @param {number} wordsPerMinute - Average reading speed (default: 200)
 * @returns {string} Reading time estimate
 */
export function getReadingTime(text, wordsPerMinute = 200) {
  if (!text || typeof text !== 'string') {
    return '0 min read'
  }
  
  // Remove HTML tags and count words
  const plainText = text.replace(/<[^>]*>/g, '')
  const wordCount = plainText.trim().split(/\s+/).length
  const minutes = Math.ceil(wordCount / wordsPerMinute)
  
  return `${minutes} min read`
}

// =======================================================================
// STRING MANIPULATION UTILITIES
// =======================================================================

/**
 * CREATE URL-FRIENDLY SLUG
 * ========================
 * 
 * Convert a string to a URL-friendly slug format.
 * 
 * @param {string} text - Text to convert
 * @returns {string} URL-friendly slug
 * 
 * EXAMPLES:
 * createSlug('My Amazing Blog Post!') → 'my-amazing-blog-post'
 * createSlug('Laravel & Svelte Tutorial') → 'laravel-svelte-tutorial'
 */
export function createSlug(text) {
  if (!text || typeof text !== 'string') {
    return ''
  }
  
  return text
    .toLowerCase()                    // Convert to lowercase
    .trim()                          // Remove leading/trailing spaces
    .replace(/[^a-z0-9\s-]/g, '')   // Remove special characters
    .replace(/\s+/g, '-')           // Replace spaces with hyphens
    .replace(/-+/g, '-')            // Replace multiple hyphens with single
    .replace(/^-|-$/g, '')          // Remove leading/trailing hyphens
}

/**
 * TRUNCATE TEXT WITH ELLIPSIS
 * ===========================
 * 
 * Truncate text to a maximum length and add ellipsis.
 * 
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum character length
 * @param {string} suffix - Suffix to add (default: '...')
 * @returns {string} Truncated text
 */
export function truncateText(text, maxLength = 100, suffix = '...') {
  if (!text || typeof text !== 'string') {
    return ''
  }
  
  if (text.length <= maxLength) {
    return text
  }
  
  // Find the last space before the cutoff to avoid cutting words
  const lastSpace = text.lastIndexOf(' ', maxLength - suffix.length)
  const cutoff = lastSpace > 0 ? lastSpace : maxLength - suffix.length
  
  return text.substring(0, cutoff).trim() + suffix
}

/**
 * CAPITALIZE FIRST LETTER
 * =======================
 * 
 * Capitalize the first letter of a string.
 * 
 * @param {string} text - Text to capitalize
 * @returns {string} Capitalized text
 */
export function capitalize(text) {
  if (!text || typeof text !== 'string') {
    return ''
  }
  
  return text.charAt(0).toUpperCase() + text.slice(1)
}

/**
 * CONVERT TO TITLE CASE
 * =====================
 * 
 * Convert text to title case (capitalize each word).
 * 
 * @param {string} text - Text to convert
 * @returns {string} Title case text
 */
export function toTitleCase(text) {
  if (!text || typeof text !== 'string') {
    return ''
  }
  
  return text
    .toLowerCase()
    .split(' ')
    .map(word => capitalize(word))
    .join(' ')
}

// =======================================================================
// VALIDATION UTILITIES
// =======================================================================

/**
 * VALIDATE EMAIL ADDRESS
 * ======================
 * 
 * Check if an email address is valid using regex.
 * 
 * @param {string} email - Email to validate
 * @returns {boolean} True if valid email
 */
export function isValidEmail(email) {
  if (!email || typeof email !== 'string') {
    return false
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email.toLowerCase())
}

/**
 * VALIDATE URL
 * ===========
 * 
 * Check if a URL is valid.
 * 
 * @param {string} url - URL to validate
 * @returns {boolean} True if valid URL
 */
export function isValidUrl(url) {
  if (!url || typeof url !== 'string') {
    return false
  }
  
  try {
    new URL(url)
    return true
  } catch {
    return false
  }
}

/**
 * VALIDATE REQUIRED FIELD
 * =======================
 * 
 * Check if a field has a value (not empty, null, or undefined).
 * 
 * @param {any} value - Value to check
 * @returns {boolean} True if field has value
 */
export function isRequired(value) {
  if (value === null || value === undefined) {
    return false
  }
  
  if (typeof value === 'string') {
    return value.trim().length > 0
  }
  
  if (Array.isArray(value)) {
    return value.length > 0
  }
  
  return true
}

/**
 * VALIDATE PASSWORD STRENGTH
 * ==========================
 * 
 * Check password strength and return score with feedback.
 * 
 * @param {string} password - Password to validate
 * @returns {object} Validation result with score and feedback
 */
export function validatePassword(password) {
  if (!password || typeof password !== 'string') {
    return { score: 0, feedback: 'Password is required' }
  }
  
  let score = 0
  const feedback = []
  
  // Length check
  if (password.length >= 8) {
    score += 1
  } else {
    feedback.push('Password must be at least 8 characters')
  }
  
  // Uppercase check
  if (/[A-Z]/.test(password)) {
    score += 1
  } else {
    feedback.push('Password must contain an uppercase letter')
  }
  
  // Lowercase check
  if (/[a-z]/.test(password)) {
    score += 1
  } else {
    feedback.push('Password must contain a lowercase letter')
  }
  
  // Number check
  if (/\d/.test(password)) {
    score += 1
  } else {
    feedback.push('Password must contain a number')
  }
  
  // Special character check
  if (/[!@#$%^&*]/.test(password)) {
    score += 1
  } else {
    feedback.push('Password must contain a special character (!@#$%^&*)')
  }
  
  const strength = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][score]
  
  return {
    score,
    strength,
    feedback: feedback.length > 0 ? feedback : ['Password meets all requirements'],
    isValid: score >= 4
  }
}

// =======================================================================
// FORMATTING UTILITIES
// =======================================================================

/**
 * FORMAT NUMBER WITH COMMAS
 * =========================
 * 
 * Format large numbers with comma separators.
 * 
 * @param {number} num - Number to format
 * @returns {string} Formatted number
 */
export function formatNumber(num) {
  if (typeof num !== 'number' || isNaN(num)) {
    return '0'
  }
  
  return num.toLocaleString()
}

/**
 * FORMAT FILE SIZE
 * ===============
 * 
 * Convert bytes to human-readable file size.
 * 
 * @param {number} bytes - File size in bytes
 * @returns {string} Formatted file size
 */
export function formatFileSize(bytes) {
  if (typeof bytes !== 'number' || isNaN(bytes) || bytes < 0) {
    return '0 B'
  }
  
  const units = ['B', 'KB', 'MB', 'GB', 'TB']
  let size = bytes
  let unitIndex = 0
  
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024
    unitIndex++
  }
  
  return `${size.toFixed(unitIndex === 0 ? 0 : 1)} ${units[unitIndex]}`
}

// =======================================================================
// DOM AND BROWSER UTILITIES
// =======================================================================

/**
 * SCROLL TO ELEMENT SMOOTHLY
 * ==========================
 * 
 * Smooth scroll to an element or position.
 * 
 * @param {string|Element} target - CSS selector or DOM element
 * @param {number} offset - Offset from top (default: 0)
 */
export function scrollToElement(target, offset = 0) {
  try {
    let element
    
    if (typeof target === 'string') {
      element = document.querySelector(target)
    } else if (target instanceof Element) {
      element = target
    } else {
      console.warn('Invalid scroll target:', target)
      return
    }
    
    if (!element) {
      console.warn('Scroll target not found:', target)
      return
    }
    
    const elementTop = element.getBoundingClientRect().top + window.pageYOffset
    const offsetPosition = elementTop - offset
    
    window.scrollTo({
      top: offsetPosition,
      behavior: 'smooth'
    })
  } catch (error) {
    console.error('Error scrolling to element:', error)
  }
}

/**
 * COPY TEXT TO CLIPBOARD
 * ======================
 * 
 * Copy text to clipboard with fallback for older browsers.
 * 
 * @param {string} text - Text to copy
 * @returns {Promise<boolean>} Success status
 */
export async function copyToClipboard(text) {
  if (!text || typeof text !== 'string') {
    return false
  }
  
  try {
    // Modern clipboard API
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text)
      return true
    }
    
    // Fallback for older browsers
    const textArea = document.createElement('textarea')
    textArea.value = text
    textArea.style.position = 'fixed'
    textArea.style.left = '-999999px'
    textArea.style.top = '-999999px'
    document.body.appendChild(textArea)
    textArea.focus()
    textArea.select()
    
    const success = document.execCommand('copy')
    document.body.removeChild(textArea)
    
    return success
  } catch (error) {
    console.error('Error copying to clipboard:', error)
    return false
  }
}

// =======================================================================
// ASYNC UTILITIES
// =======================================================================

/**
 * DELAY EXECUTION
 * ==============
 * 
 * Simple promise-based delay function.
 * 
 * @param {number} ms - Milliseconds to delay
 * @returns {Promise} Promise that resolves after delay
 */
export function delay(ms) {
  return new Promise(resolve => setTimeout(resolve, ms))
}

/**
 * DEBOUNCE FUNCTION
 * ================
 * 
 * Debounce a function to limit how often it can be called.
 * Useful for search inputs, scroll handlers, etc.
 * 
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} Debounced function
 */
export function debounce(func, wait) {
  let timeout
  
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

/**
 * THROTTLE FUNCTION
 * ================
 * 
 * Throttle a function to limit how often it can be called.
 * Different from debounce - ensures function is called at regular intervals.
 * 
 * @param {Function} func - Function to throttle
 * @param {number} limit - Time limit in milliseconds
 * @returns {Function} Throttled function
 */
export function throttle(func, limit) {
  let inThrottle
  
  return function executedFunction(...args) {
    if (!inThrottle) {
      func.apply(this, args)
      inThrottle = true
      setTimeout(() => inThrottle = false, limit)
    }
  }
}

// =======================================================================
// ERROR HANDLING UTILITIES
// =======================================================================

/**
 * SAFE JSON PARSE
 * ==============
 * 
 * Parse JSON with fallback for invalid JSON.
 * 
 * @param {string} jsonString - JSON string to parse
 * @param {any} fallback - Fallback value if parsing fails
 * @returns {any} Parsed object or fallback
 */
export function safeJsonParse(jsonString, fallback = null) {
  try {
    return JSON.parse(jsonString)
  } catch (error) {
    console.warn('Failed to parse JSON:', error)
    return fallback
  }
}

/**
 * HANDLE ASYNC ERRORS
 * ===================
 * 
 * Wrapper for async functions that handles errors gracefully.
 * 
 * @param {Function} asyncFn - Async function to wrap
 * @param {any} fallback - Fallback value on error
 * @returns {Function} Error-safe async function
 */
export function withErrorHandling(asyncFn, fallback = null) {
  return async (...args) => {
    try {
      return await asyncFn(...args)
    } catch (error) {
      console.error('Async function error:', error)
      return fallback
    }
  }
} 