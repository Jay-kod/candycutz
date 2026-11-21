import DOMPurify from 'dompurify';

const ALLOWED_TAGS = [
  'a',
  'blockquote',
  'br',
  'code',
  'del',
  'em',
  'h1',
  'h2',
  'h3',
  'h4',
  'h5',
  'h6',
  'hr',
  'i',
  'img',
  'li',
  'ol',
  'p',
  'pre',
  's',
  'strong',
  'ul',
  'u',
];

const ALLOWED_ATTR = ['alt', 'height', 'href', 'rel', 'src', 'target', 'title', 'width'];

export function sanitizeBlogContent(content) {
  return DOMPurify.sanitize(typeof content === 'string' ? content : '', {
    ALLOWED_TAGS,
    ALLOWED_ATTR,
  });
}
