import { describe, it, expect } from 'vitest';
import { mount } from '@vue/test-utils';
import TestimonialsMarquee from '../TestimonialsMarquee.vue';

describe('TestimonialsMarquee', () => {
  it('renders section title and client stories badge', () => {
    const wrapper = mount(TestimonialsMarquee, {
      props: {
        title: 'What our clients say',
        badge: 'Client stories',
      },
    });

    expect(wrapper.text()).toContain('Client stories');
    expect(wrapper.text()).toContain('What our clients say');
  });

  it('renders an empty state when there are no approved visible testimonials', () => {
    const wrapper = mount(TestimonialsMarquee, {
      props: {
        testimonials: [],
      },
    });

    expect(wrapper.text()).toContain('Approved customer testimonials will appear here.');
    expect(wrapper.findAll('article')).toHaveLength(0);
  });

  it('renders each approved and visible testimonial once', () => {
    const testimonials = [
      { id: 'shown', client_name: 'Approved Client', review: 'Great cut.', is_approved: true, is_featured: true },
      { id: 'hidden', client_name: 'Hidden Client', review: 'Hidden cut.', is_approved: true, is_featured: false },
      { id: 'pending', client_name: 'Pending Client', review: 'Pending cut.', is_approved: false, is_featured: true },
    ];

    const wrapper = mount(TestimonialsMarquee, {
      props: {
        testimonials,
      },
    });

    expect(wrapper.text()).toContain('Approved Client');
    expect(wrapper.text()).not.toContain('Hidden Client');
    expect(wrapper.text()).not.toContain('Pending Client');
    expect(wrapper.findAll('article')).toHaveLength(1);
  });

  it('applies flow direction and animation speed styles to marquee track', () => {
    const testimonials = [
      { id: 'shown', client_name: 'Approved Client', review: 'Great cut.', is_approved: true, is_featured: true },
    ];

    const wrapper = mount(TestimonialsMarquee, {
      props: {
        testimonials,
        speed: 30,
        direction: 'right',
      },
    });

    const track = wrapper.find('.marquee-track');
    expect(track.exists()).toBe(true);
    expect(track.classes()).toContain('marquee-track-right');
    expect(track.attributes('style')).toContain('animation-duration: 30s');
  });
});
