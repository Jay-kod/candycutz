import { describe, expect, it } from 'vitest';
import { getApiCollection } from '../getApiCollection';

describe('getApiCollection', () => {
  it('reads a paginated API resource from the response envelope', () => {
    const posts = [{ id: 1 }, { id: 2 }];

    expect(getApiCollection({ data: { data: { data: posts } } })).toEqual(posts);
  });

  it('reads a non-paginated collection from the response envelope', () => {
    const posts = [{ id: 1 }];

    expect(getApiCollection({ data: { data: posts } })).toEqual(posts);
  });

  it('returns an empty collection for invalid response shapes', () => {
    expect(getApiCollection({ data: { data: null } })).toEqual([]);
  });
});
